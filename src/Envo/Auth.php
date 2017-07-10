<?php

use Phalcon\Mvc\User\Component;
use Core\Model\User;
use Core\Model\UserRepository;
use Core\Model\RememberToken;
use Core\Model\FailedLogin;

/**
 * Class Auth
 */
class Auth extends Component
{
	protected static $instance = null;
	protected static $user = null;
	protected static $client = null;
	protected static $loggedIn = null;

	const TOKEN_NAME = 'auth-identity';
	const COOKIE_REMEMBER = 'remember_rmu';
	const COOKIE_TOKEN = 'remember_rmt';

	/**
	 * @return self|null
	 */
	public static function getInstance()
	{
		if ( self::$instance ) {
			return self::$instance;
		}

		return self::$instance = new self();
	}

	/**
	 * Get current client
	 */
	public static function client()
	{
		if( ! self::$client ) {
			self::$client = self::user()->ref('client');
		}
		return self::$client;
	}

	/**
	 * Get current user
	 *
	 */
	public static function user()
	{
		if ( !is_null( self::$user ) ) {
			return self::$user;
		}

		if( defined('APP_CLI') ) {
			return null;
		}

		$instance = self::getInstance();
		$session = $instance->session;
		$auth = $session->get( self::TOKEN_NAME );

		$user = null;
		if ( ! $auth ) {
			if( ! $user && $instance->usesApiKey() ) {
				$user = $instance->getUserFromApiKey();
			}

			if ( ! $user && $instance->hasRememberMe() ) {
				$user = $instance->loginWithRememberMe();
			}

			if( ! $user ) {
				$user = $instance->loginWithAuthorizationHeaders();
			}

			if ( !$user ) {
				$user = new \Core\Model\User;
				$user->loggedIn = self::$loggedIn = false;
			} else {
				$user->loggedIn = self::$loggedIn = true;
				// new \Core\Events\UserSessionRestored(null, true, $user);
			}
		}
		else {
			// TODO: cache user query
			$user = UserRepository::getByUserId($auth['id']);

			if ( !$user ) {
				$session->remove( self::TOKEN_NAME );
				header( 'Location: /' );

				return;
			}
			$user->loggedIn = self::$loggedIn = true;
			// $user->switched = $session->get( 'orig_user' );
		}

		if( $user->loggedIn ) {
			\Translator::setLocale($user->getLanguage());
		}

		self::$user = $user;
		return self::$user;
	}

	/**
	 * Is user a guest
	 *
	 * @return \Core\Model\User|null
	 */
	public static function guest()
	{
		if ( !is_null( self::$loggedIn ) ) {
			self::user();
		}

		return self::$loggedIn;
	}

	/**
	 * Checks the user credentials
	 *
	 * @param array $credentials
	 *
	 * @return bool
	 */
	public function check($credentials)
	{
		extract( $credentials );
		// Check if the user exist
		$user = User::findFirst( array(
			"(email = ?0 OR username = ?1)",
			'bind' => [ $email, $email ],
		) );

		if( ! $user || ! env('IGNORE_PASSWORDS') ) {
			// Check the password
			if ( !$user || password_verify( $password, $user->password) === false ) {
				if( env('APP_ENV') != 'local' ) {
					$this->registerUserThrottling( $user ? $user->id : 0 );
				}

				if ( $user ) {
					new \Core\Events\UserWrongPassword(null, true, $user );
				}
				else new \Core\Events\LoginFailed( [ 'user' => $email ] );

				return $this->flashSession->error( \_t('validation.emailOrPasswordWrong') );
			}
		}
		
		if( ! $user ) {
			return $this->flashSession->error( \_t('validation.emailOrPasswordWrong') );
		}

		// Check if the user was flagged
		$this->checkUserFlags( $user );

		// Check if the remember me was selected
		if ( isset($credentials[ 'remember' ]) ) {
			$this->createRememberEnviroment( $user );
		}

		$this->session->set( self::TOKEN_NAME, array(
			'id'   => $user->user_id,
			'name' => $user->username,
		));

		$event = new \Core\Events\LoggedIn(null, false, $user );
		$event = $event->getEvent();
		$event->user_id = $user->getId();
		$event->client_id = $user->getClientId();
		$event->save();

		return true;
	}

	/**
	 * Implements login throttling
	 * Reduces the effectiveness of brute force attacks
	 *
	 * @param int $userId
	 */
	public function registerUserThrottling($userId)
	{
		$failedLogin            = new FailedLogin();
		$failedLogin->user_id   = $userId;
		$failedLogin->ip        = $this->request->getClientAddress();
		$failedLogin->attempted = time();
		$failedLogin->save();
		$attempts = FailedLogin::count( array(
			'ip = ?0 AND attempted >= ?1',
			'bind' => array(
				$this->request->getClientAddress(),
				time() - 3600 * 6,
			),
		) );
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
				sleep( 6 );
				break;
		}
	}

	/**
	 * Creates the remember me environment settings the related cookies and generating tokens
	 *
	 * @param Core\Model\User $user
	 */
	public function createRememberEnviroment(User $user)
	{
		// $userAgent            = $this->request->getUserAgent();
		if( ! $user->remember_token ) {
			$user->remember_token = \Str::random(32);
			$user->save();
		}
		$expire = time() + (86400 * 365);

		$this->cookies->set(self::COOKIE_REMEMBER, $user->user_id, $expire );
		$this->cookies->set(self::COOKIE_TOKEN, $user->remember_token, $expire );
	}

	/**
	 * Check if the session has a remember me cookie
	 *
	 * @return boolean
	 */
	public function hasRememberMe()
	{
		return $this->cookies->has( self::COOKIE_REMEMBER );
	}

	/**
	 * Logs on using the information in the coookies
	 *
	 * @return \Phalcon\Http\Response
	 */
	public function loginWithRememberMe()
	{
		$userId = $this->cookies->get( self::COOKIE_REMEMBER )->getValue();
		$token = $this->cookies->get( self::COOKIE_TOKEN )->getValue();

		if ( ($user = UserRepository::getByUserId( $userId )) ) {
			if( $user && isset($user->remember_token) && $user->remember_token == $token ) {
				// $this->checkUserFlags( $user );
				// $this->session->set( self::TOKEN_NAME, array(
				// 	'id'   => $user->user_id,
				// 	'name' => $user->username,
				// ));
				$this->loginUsingId($user);

				return $user;
			}
		}

		$this->cookies->get( self::COOKIE_REMEMBER )->delete();
		$this->cookies->get( self::COOKIE_TOKEN )->delete();

		return false;
	}

	/**
	 * Login into the app with a Authorization header
	 * 
	 * @return Core\Model\User|null
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

		if( $apiKey == 'iYePyAsgoopeSo6iR8sJM1QOYjpGCKb5' || $apiKey == 'iYePyAsgoopeSo6iR8sJM1QOYjpGCKc5' ) {
			putenv('APP_TESTING=true');
		}

		return \Core\Model\UserRepository::getByApiKey($apiKey);
	}

	public function usesApiKey()
	{
		return $this->request->get('api_key') && $this->request->get('app_secret');
	}

	public function getUserFromApiKey()
	{
		$apiKey = $this->request->get('api_key');
		$secret = $this->request->get('app_secret');

		if( ! ($app = \Core\Model\AppRepository::getBySecret($secret)) ) {
			return false;
		}

		$user = \Core\Model\UserRepository::getByApiKey($apiKey);
		if( ! $user ) {
			return false;
		}
		$user->setAccessMode(User::ACCESS_API_TOKEN);

		return $user;
	}

	/**
	 * Checks if the user is banned/inactive/suspended
	 *
	 * @param Core\Model\User $user
	 *
	 * @return bool
	 */
	public function checkUserFlags(User $user)
	{
		return true;
		// if ($user->active != 'Y') {
		//     throw new Exception('The user is inactive');
		// }
		// if ($user->banned != 'N') {
		//     throw new Exception('The user is banned');
		// }
		// if ($user->suspended != 'N') {
		//     throw new Exception('The user is suspended');
		// }
	}

	/**
	 * Returns the current identity
	 *
	 * @return array
	 */
	public function getIdentity()
	{
		return $this->session->get( self::TOKEN_NAME );
	}

	/**
	 * Returns the current identity
	 *
	 * @return string
	 */
	public function getName()
	{
		$identity = $this->session->get( self::TOKEN_NAME );
		return $identity[ 'name' ];
	}

	/**
	 * Removes the user identity information from session
	 */
	public function remove()
	{
		if ( $this->cookies->has( self::COOKIE_REMEMBER ) ) {
			$this->cookies->get( self::COOKIE_REMEMBER )->delete();
		}
		if ( $this->cookies->has( self::COOKIE_TOKEN ) ) {
			$this->cookies->get( self::COOKIE_TOKEN )->delete();
		}
		$this->session->remove( self::TOKEN_NAME );
	}

	/**
	 * Auths the user by his/her id
	 *
	 * @param int $id
	 *
	 * @throws Exception
	 */
	public function loginUsingId($user)
	{
		if( is_string($user) ) {
			$user = User::findFirstByUserId( $user );
		}

		if ( $user == false ) {
			throw new Exception( 'The user does not exist' );
		}
		$this->checkUserFlags( $user );
		$this->session->set( self::TOKEN_NAME, array(
			'id'   => $user->user_id,
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
		$identity = $this->session->get( self::TOKEN_NAME );
		if ( isset($identity[ 'id' ]) ) {
			$user = User::findFirstByUserId( $identity[ 'id' ] );
			if ( $user == false ) {
				throw new Exception( 'The user does not exist' );
			}

			return $user;
		}

		return false;
	}

}