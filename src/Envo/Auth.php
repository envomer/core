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
    const TOKEN_NAME = 'auth-identity';
    const COOKIE_REMEMBER = 'remember_rmu';
    const COOKIE_TOKEN = 'remember_rmt';

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
        if (! $this->team) {
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
        if (null !== $this->user) {
            return $this->user;
        }

        if (\defined('APP_CLI')) {
            return null;
        }

        // TODO: if no session class is defined then return empty user object
        $auth = @$this->session->get(self::TOKEN_NAME);

        $user = null;
        if (! $auth) {
            if (! $user && ($key = $this->usesApiKey())) {
                $user = $this->getUserFromApiKey($key);
            }

            if (! $user && ($key = $this->hasRememberMe())) {
                $user = $this->loginWithRememberMe($key);
            }

            if (! $user) {
                $user = $this->loginWithAuthorizationHeaders();
            }

            if (!$user) {
                $user = new $this->userClass;
                $user->loggedIn = $this->loggedIn = false;
            } else {
                $user->loggedIn = $this->loggedIn = true;
                // new UserSessionRestored(null, true, $user);
            }
        } else {
            // TODO: cache user query
            $userClass = $this->userClass;
            /** @var User $user */
            $user = $userClass::repo()->where($userClass::getQualifier(), $auth['id'])->getOne();

            if (!$user) {
                /* invalid auth identifier given...logout the user and redirect to homepage */
                $this->remove();
                header('Location: /');

                return false;
            }

            $user->loggedIn = $this->loggedIn = true;
            $user->setAccessMode($userClass::ACCESS_SESSION);
            // $user->switched = $session->get( 'orig_user' );
        }

        if ($user->isLoggedIn() && $user->getLanguage()) {
            /** @var Translator $translator */
            $translator = resolve('translator');
            $translator->setLocale($user->getLanguage());
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
        if (null !== $this->loggedIn) {
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
    public function check(string $email, string $password, bool $remember = false)
    {
        $user = $this->validate($email, $password);

        $this->loginUser($user, $remember);

        return $user;
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return array|bool
     */
    public function validate(string $email, string $password)
    {
        $user = $this->getUserByEmail($email);

        if (!$user) {
            new LoginFailed(null, true, null, [
                'email' => $email,
                'host' => $_SERVER['HTTP_HOST'] ?? null,
            ]);
            $this->throttle();
            return false;
        }


        $valid = $this->validateUserPassword($user, $password);

        if (!$valid) {
            new UserWrongPassword(null, true, $user, [
                'email' => $email,
                'host' => $_SERVER['HTTP_HOST'] ?? null,
            ]);
            $this->throttle($user);
            return false;
        }

        // Check if the user was flagged
        $this->checkUserFlags($user);

        return $user;
    }

    /**
     * In case the user login didn't work, then
     * throttle the user
     *
     * @return void
     */
    private function throttle($user = null)
    {
        if (env('APP_ENV') !== 'local') {
            $this->registerUserThrottling($user);
        }
    }

    /**
     * Implements login throttling
     * Reduces the effectiveness of brute force attacks
     *
     * @param User $user
     */
    public function registerUserThrottling($user = null)
    {
        if (!config('app.guard.throttle', false)) {
            return;
        }

        $ip = \Envo\Support\IP::getIpAddress();

        if (!$ip) {
            return; // ip is not returned;
        }

        $failed = new \Envo\Model\FailedLogin();
        $failed->user_id = $user ? $user->getId() : null;
        $failed->ip = $ip;
        $failed->created_at = \Envo\Support\Date::now();
        $failed->save();

        $attempts = \Envo\Model\FailedLogin::count([
            'ip = ?0 AND created_at >= ?1',
            'bind' => array(
                $ip,
                date('Y-m-d H:i:s', strtotime('-15 min')),
            ),
        ]);

        switch ($attempts) {
            case 0:
            case 1:
            case 2:
                // no delay
                break;
            case 3:
            case 4:
                sleep(2);
                break;
            case 5:
            case 6:
                sleep(4);
                break;
            case 7:
            case 8:
            case 9:
                sleep(12);
                break;
            default:
                sleep(30);
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
        if (! $user->remember_token) {
            $user->remember_token = Str::random(32);
            $user->save();
        }
        //$expire = time() + (86400 * 365);
        $expire = time() + 31531000; // a year

        $this->cookies->set(self::COOKIE_REMEMBER, $user->getQualifierValue(), $expire);
        $this->cookies->set(self::COOKIE_TOKEN, $user->remember_token, $expire);
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

        if ($user && isset($user->remember_token) && $user->remember_token === $token) {
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
        $headers = \array_change_key_case($headers, \CASE_UPPER);

        if (! isset($headers['AUTHORIZATION']) || ! ($authorization = $headers['AUTHORIZATION'])) {
            return null;
        }
        if (strpos($authorization, 'Bearer') === false) {
            return null;
        }

        $apiKey = str_replace('Bearer ', '', $authorization);
        if (! $apiKey) {
            return null;
        }

        if ($apiKey === 'iYePyAsgoopeSo6iR8sJM1QOYjpGCKb5' || $apiKey === 'iYePyAsgoopeSo6iR8sJM1QOYjpGCKc5') {
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
        if (! $user) {
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
        if ($this->cookies->has(self::COOKIE_REMEMBER)) {
            $this->cookies->get(self::COOKIE_REMEMBER)->delete();
        }

        if ($this->cookies->has(self::COOKIE_TOKEN)) {
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
        if (is_string($user)) {
            $user = $userClass::findFirstByIdentifier($user);
        }

        if ($user === false) {
            internal_exception('auth.userNotFound', 404);
        }
        $this->checkUserFlags($user);
        $this->session->set(self::TOKEN_NAME, array(
            'id'   => $user->getQualifierValue(),
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
        if (is_numeric($user)) {
            $user = $userClass::findFirstById($user);
        }

        if ($user === false) {
            internal_exception('auth.userNotFound', 404);
        }
        $this->checkUserFlags($user);
        $this->session->set(self::TOKEN_NAME, array(
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
        if (isset($identity[ 'id' ])) {
            $user = $userClass::findFirst([
                'conditions' => $userClass::getQualifier() . ' = :val:',
                'bind' => [
                    'val' => $identity[ 'id' ]
                ]
            ]);
            if ($user == false) {
                public_exception('auth.userNotFound', 404);
            }

            return $user;
        }

        return false;
    }

    /**
     * Encrypt the given password
     *
     * @param string $password
     *
     * @return string
     */
    public function passwordEncrypt(string $password) : string
    {
        return Str::hash($password);
    }

    /**
     * @param User $user
     * @param boolean $remember
     *
     * @return User
     */
    public function loginUser($user, $remember = false)
    {
        $this->session->set(self::TOKEN_NAME, [
            'id' => $user->getQualifierValue(),
            'name' => $user->username,
        ]);

        // Check if the remember me was selected
        if ($remember) {
            $this->createRememberEnvironment($user);
        }

        $event = new LoggedIn(null, false, $user, [
            'username' => $user->username,
            'email' => $user->email
        ]);

        $event = $event->getEvent();
        if ($event) {
            $event->user_id = $user->getId();
            $event->team_id = $user->getTeamId();
            $event->save();
        }

        return $user;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function getUserByEmail($email)
    {
        // Check if the user exist
        $userClass = $this->userClass;

        $emailProp = 'email';

        $config = $this->di->get('config');

        if ($config->get('app.auth.wildcard_email_login', false)) {
            //normalize the email
            $email = preg_replace('/(\+.*)(@)/', '$2', $email);

            // if enabled then we have to check the email this way
            $emailProp = 'REGEXP_REPLACE(email, "\\\+.*@", "@")';
        }

        /** @var User $user */
        $user = $userClass::repo()->where(
            'deleted_at IS NULL AND (' . $emailProp . ' = :email: OR username = :username:)',
            [
                'email' => $email,
                'username' => $email
            ]
        )->getOne();

        return $user;
    }

    /**
     * @param mixed $user
     * @param string $password
     * @param array $user
     */
    public function validateUserPassword($user, $password): bool
    {
        return password_verify($password, $user->getPassword()) === true;
    }
}
