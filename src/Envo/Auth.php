<?php

namespace Envo;

use Envo\Event\Model\Event;
use Envo\Model\User;

use Envo\Model\Team;
use Envo\Event\LoginFailed;
use Envo\Event\UserWrongPassword;
use Envo\Event\LoggedIn;

use Envo\Support\Str;
use Envo\Support\Translator;
use Phalcon\Mvc\User\Component;

/**
 * Class Auth
 */
class Auth extends Component
{
	/**
	 * @var User
	 */
	protected $user;
	
	/**
	 * @var Team
	 */
	protected $team;
	
	/**
	 * @var bool
	 */
	protected $loggedIn;
	
	/**
	 * @var string
	 */
	protected $userClass;
	
	/**
	 * @var string
	 */
	protected $teamClass;

	const TOKEN_NAME = 'auth-identity';
	const COOKIE_REMEMBER = 'remember_rmu';
	const COOKIE_TOKEN = 'remember_rmt';

	public $authKey = 'id';
	
	/**
	 * Auth constructor.
	 */
	public function __construct()
	{
		$this->userClass = config('app.classmap.user', User::class);
		$this->teamClass = config('app.classmap.team', Team::class);
	}
	
	/**
	 * Get current team
	 *
	 * @return Team
	 * @throws AbstractException
	 */
	public function team()
	{
		if( ! $this->team ) {
			$this->team = $this->user()->ref('team');
		}

		return $this->team;
	}
	
	/**
	 * Get current user
	 *
	 * @return User|bool
	 * @throws AbstractException
	 */
	public function user()
	{
		if ( null !== $this->user ) {
			return $this->user;
		}

		if( defined('APP_CLI') ) {
			return null;
		}

		// TODO: if no session class is defined then return empty user object

		$auth = $this->session->get(self::TOKEN_NAME);

		$user = null;
		if ( ! $auth ) {
			if( ! $user && ($key = $this->usesApiKey())) {
				$user = $this->getUserFromApiKey($key);
			}
			
			if ( ! $user && ($key = $this->hasRememberMe())) {
				$user = $this->loginWithRememberMe($key);
			}

			if( ! $user ) {
				$user = $this->loginWithAuthorizationHeaders();
			}

			if ( !$user ) {
				$user = new $this->userClass;
				$user->loggedIn = $this->loggedIn = false;
			} else {
				$user->loggedIn = $this->loggedIn = true;
				// new UserSessionRestored(null, true, $user);
			}
		}
		else {
			// TODO: cache user query
			$userClass = $this->userClass;
			/** @var User $user */
			$user = $userClass::repo()->where($userClass::getQualifier(), $auth['id'])->getOne();

			if ( !$user ) {
				$this->remove();
				//$this->session->remove(self::TOKEN_NAME);
				header('Location: /');

				return false;
			}
			$user->loggedIn = $this->loggedIn = true;
			$user->setAccessMode($userClass::ACCESS_SESSION);
			// $user->switched = $session->get( 'orig_user' );
		}

		 if( $user->isLoggedIn() && $user->getLanguage() ) {
			 resolve('translator')->setLocale($user->getLanguage());
		 }
		
		return $this->user = $user;
	}
	
	/**
	 * Is user a guest
	 *
	 * @return bool
	 * @throws AbstractException
	 */
	public function guest()
	{
		if ( null !== $this->loggedIn ) {
			$this->user();
		}

		return $this->loggedIn;
	}
	
	/**
	 * Checks the user credentials
	 *
	 * @param      $email
	 * @param      $password
	 *
	 * @param bool $remember
	 *
	 * @return bool
	 * @throws \RuntimeException
	 * @throws Exception\PublicException
	 */
	public function check($email, $password, $remember = false)
	{
		// Check if the user exist
		$userClass = $this->userClass;
		/** @var User $user */
		$user = $userClass::repo()->where('email = :email: OR username = :username:',[
			'email' => $email,
			'username' => $email
		])->getOne();
		
		if(
			(! $user || ! env('IGNORE_PASSWORDS'))
			&& (!$user || password_verify($password, $user->getPassword()) === false)
		) {
			if(env('APP_ENV') !== 'local') {
				$this->registerUserThrottling($user);
			}

			if ($user) {
				new UserWrongPassword(null, true, $user);
			} else {
				new LoginFailed(['user' => $email]);
			}

			public_exception('validation.emailOrPasswordWrong', 400);
		}
		
		if( ! $user ) {
			public_exception('validation.emailOrPasswordWrong', 400);
		}

		// Check if the user was flagged
		$this->checkUserFlags( $user );

		// Check if the remember me was selected
		if ($remember) {
			$this->createRememberEnvironment( $user );
		}

		$this->session->set(self::TOKEN_NAME, [
			'id'   => $user->getQualifierValue(),
			'name' => $user->username,
		]);

		$event = new LoggedIn(null, false, $user);
		$event = $event->getEvent();
		if($event) {
			$event->user_id = $user->getId();
			$event->team_id = $user->getTeamId();
			$event->save();
		}

		return true;
	}

	/**
	 * Implements login throttling
	 * Reduces the effectiveness of brute force attacks
	 *
	 * @param User $user
	 */
	public function registerUserThrottling($user = null)
	{
		$loginFailedEvent = new LoginFailed(null, true, $user);
		$attempts = Event::count([
			'ip_id = ?0 AND created_at >= ?1',
			'bind' => array(
				$loginFailedEvent->getEvent()->ip_id,
				date('Y-m-d H:i:s', strtotime('-15 min')),
			),
		]);
		
		switch ($attempts) {
			case 1:
			case 2:
				// no delay
				break;
			case 3:
			case 4:
				sleep( 2 );
				break;
			case 5:
				sleep( 4 );
				break;
			default:
				sleep( 12 );
				break;
		}
	}
	
	/**
	 * Creates the remember me environment settings the related cookies and generating tokens
	 *
	 * @param User $user
	 *
	 * @throws \RuntimeException
	 */
	public function createRememberEnvironment($user)
	{
		// $userAgent            = $this->request->getUserAgent();
		if( ! $user->remember_token ) {
			$user->remember_token = Str::random(32);
			$user->save();
		}
		//$expire = time() + (86400 * 365);
		$expire = time() + 31531000; // a year

		$this->cookies->set(self::COOKIE_REMEMBER, $user->getQualifierValue(), $expire );
		$this->cookies->set(self::COOKIE_TOKEN, $user->remember_token, $expire );
	}

	/**
	 * Check if the session has a remember me cookie
	 *
	 * @return boolean
	 */
	public function hasRememberMe()
	{
		$cookie = $this->cookies->get(self::COOKIE_REMEMBER);
		return $cookie ? $cookie->getValue() : null;
	}
	
	/**
	 * Logs on using the information in the cookies
	 *
	 * @param $userId
	 *
	 * @return bool|User
	 * @throws AbstractException
	 */
	public function loginWithRememberMe($userId = null)
	{
		$userId = $userId ?: $this->cookies->get(self::COOKIE_REMEMBER)->getValue();
		$token = $this->cookies->get(self::COOKIE_TOKEN)->getValue();
		
		$userModel = $this->userClass;
		$user = $userModel::repo()->where('identifier', $userId)->getOne();

		if ( $user && isset($user->remember_token) && $user->remember_token === $token ) {
			$this->checkUserFlags($user);
			$this->loginUsingIdentifier($user);

			return $user;
		}

		$this->cookies->get(self::COOKIE_REMEMBER)->delete();
		$this->cookies->get(self::COOKIE_TOKEN)->delete();

		return false;
	}

	/**
	 * Login into the app with a Authorization header
	 * 
	 * @return User|null
	 */
	public function loginWithAuthorizationHeaders()
	{
		$headers = apache_request_headers();
		if( ! isset($headers['Authorization']) || ! ($authorization = $headers['Authorization']) ) {
			return null;
		}
		
		if( strpos($authorization, 'Bearer') === false ) {
			return null;
		}
		
		$apiKey = str_replace('Bearer ', '', $authorization);
		if( ! $apiKey ) {
			return null;
		}

		if( $apiKey === 'iYePyAsgoopeSo6iR8sJM1QOYjpGCKb5' || $apiKey === 'iYePyAsgoopeSo6iR8sJM1QOYjpGCKc5' ) {
			putenv('APP_TESTING=true');
		}

		$userClass = $this->userClass;
		return $userClass::repo()->where('api_key', $apiKey)->getOne();
	}

	/**
	 * Check if user is using api key
	 *
	 * @return string
	 */
	public function usesApiKey()
	{
		return $this->request->get('api_key');
	}
	
	/**
	 * Get user from api key
	 *
	 * @param $apiKey
	 *
	 * @return bool|User
	 */
	public function getUserFromApiKey($apiKey)
	{
		$apiKey = $apiKey ?: $this->request->get('api_key');

		$userClass = $this->userClass;
		/** @var User $user */
		$user = $userClass::repo()->where('api_key', $apiKey)->getOne();
		if( ! $user ) {
			return false;
		}
		$user->setAccessMode($userClass::ACCESS_API_TOKEN);

		return $user;
	}

	/**
	 * Checks if the user is banned/inactive/suspended
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function checkUserFlags($user)
	{
		return ! $user->isActive();
	}

	/**
	 * Returns the current identity
	 *
	 * @return array
	 */
	public function getIdentity()
	{
		return $this->session->get(self::TOKEN_NAME);
	}

	/**
	 * Returns the current identity
	 *
	 * @return string
	 */
	public function getName()
	{
		$identity = $this->session->get(self::TOKEN_NAME);
		return $identity[ 'name' ];
	}

	/**
	 * Removes the user identity information from session
	 */
	public function remove()
	{
		if ( $this->cookies->has(self::COOKIE_REMEMBER) ) {
			$this->cookies->get(self::COOKIE_REMEMBER)->delete();
		}
		
		if ( $this->cookies->has(self::COOKIE_TOKEN) ) {
			$this->cookies->get(self::COOKIE_TOKEN)->delete();
		}
		
		$this->session->remove(self::TOKEN_NAME);
	}
	
	/**
	 * Auth the user by their id
	 *
	 * @param int|string $user
	 *
	 * @return int|string
	 * @throws AbstractException
	 */
	public function loginUsingIdentifier($user)
	{
		$userClass = $this->userClass;
		if( is_string($user) ) {
			$user = $userClass::findFirstByIdentifier( $user );
		}

		if ( $user === false ) {
			internal_exception('auth.userNotFound', 404);
		}
		$this->checkUserFlags( $user );
		$this->session->set( self::TOKEN_NAME, array(
			'id'   => $user->getQualifier(),
			'name' => $user->username,
		));

		return $user;
	}
	
	/**
	 * Login the user by their id
	 *
	 * @param int $user
	 *
	 * @return int|User
	 * @throws AbstractException
	 */
	public function loginUsingId($user)
	{
		$userClass = $this->userClass;
		if( is_numeric($user) ) {
			$user = $userClass::findFirstById($user);
		}

		if ( $user === false ) {
			internal_exception('auth.userNotFound', 404);
		}
		$this->checkUserFlags( $user );
		$this->session->set( self::TOKEN_NAME, array(
			'id'   => $user->getQualifierValue(),
			'name' => $user->username,
		));

		return $user;
	}

	/**
	 * Get the entity related to user in the active identity
	 *
	 * @return User
	 * @throws Exception
	 */
	public function getUser()
	{
		$userClass = $this->userClass;
		$identity = $this->session->get(self::TOKEN_NAME);
		if ( isset($identity[ 'id' ]) ) {
			$user = $userClass::findFirst([
				'conditions' => $userClass::getQualifier() . ' = :val:',
				'bind' => [
					'val' => $identity[ 'id' ]
				]
			]);
			if ( $user == false ) {
				public_exception('auth.userNotFound', 404);
			}

			return $user;
		}

		return false;
	}
}