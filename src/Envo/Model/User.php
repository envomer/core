<?php

namespace Envo\Model;

use Envo\AbstractModel;
use Envo\Model\Traits\LegalEntityTrait;
use Envo\Support\Translator;

class User extends AbstractModel
{
    const ACCESS_API_TOKEN = 1;
    const ACCESS_SESSION = 2;
    
    const STATUS_ACTIVE = 1;
    const STATUS_BANNED = 5;
    const STATUS_SUSPENDED = 6;

    use LegalEntityTrait;
    
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'core_users';

    /**
     * Define the method the user used to log in
     *
     * @var integer
     */
    protected $accessMode;

    /**
     * Whether the user is logged in
     *
     * @var boolean
     */
    public $loggedIn = false;

    /**
     * Define the default language for user
     *
     * @var integer
     */
    public $language = Translator::LANG_DE;

    /**
     * Identifier of user
     *
     * @var string
     */
    public $identifier;

    /**
     * Username
     * 
     * @var string
     */
    public $username;
	
	/**
	 * @var int
	 */
    protected $team_id;
	
	/**
	 * @var string
	 */
    protected $api_key;
	
	/**
	 * @var int
	 */
    protected $level;
	
	/**
	 * @var int
	 */
    protected $status;
	
	/**
	 * @var string
	 */
    protected $password;
	
	/**
	 * @var string
	 */
    public $remember_token;
	
	/**
	 * @var string
	 */
    private $permissionString;
	
	/**
	 * Initialize
	 */
    public function initialize()
	{
		$this->softDeletes = false;
	}

    /**
     * Is admin flag
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->level === 9;
    }

    /**
     * Is logged in
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->loggedIn;
    }
	
	/**
	 * @return bool
	 */
	public function isBanned()
	{
		return $this->status === self::STATUS_BANNED;
	}
	
	/**
	 * @return bool
	 */
	public function isSuspended()
	{
		return $this->status === self::STATUS_SUSPENDED;
	}
	
	/**
	 * @return bool
	 */
	public function isActive()
	{
		return $this->status === self::STATUS_ACTIVE || $this->status === null;
	}

    /**
     * Set access mode.
     * ApiKey or normal log in (session)
     *
     * @param integer $mode
     * @return void
     */
    public function setAccessMode($mode)
	{
		$this->accessMode = $mode;
	}

    /**
     * Get language
     *
     * @return integer
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Get team id
     *
     * @return int
     */
    public function getTeamId()
    {
        return $this->team_id;
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Get api key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }
	
	/**
	 * @param $permissionKey
	 *
	 * @return bool
	 */
    public function can($permissionKey)
    {
        if($this->level !== null && $this->level === 9) {
            return true;
        }

        return $this->getDI()->get('permission')->can($this, $permissionKey);
    }
	
	/**
	 * @return array
	 */
    public function getPermissions()
	{
		if( isset($this->cachedRelations['permissions']) ) {
			return $this->cachedRelations['permissions'];
		}

		$permissions = $this->getDI()->get('permission')->getByUserId($this->getId()) ?: [];
		return $this->cachedRelations['permissions'] = $permissions;
	}
	
	/**
	 * @return string
	 */
	public function getPermissionPublicKey()
	{
		return $this->getDI()->get('permission')->getPublicKey($this);
	}
	
	/**
	 * @return array
	 */
	public function getPermissionKeys()
	{
		return $this->getDI()->get('permission')->getKeysByUser($this);
	}
	
	/**
	 * get a name representation of this entity
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->username;
	}
	
	/**
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}
	
	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}
	
	public function getPermissionString()
	{
		if (! $this->permissionString){
			/* permissions are not fetched yet so do it now*/
			/** @var \Envo\Model\Repository\User $repo */
			$repo = self::repo();
			$permissions = $repo->getPermissions($this->getId());
			
			
		}
		
		return $this->permissionString;
	}
	
	/**
	 * @return string
	 */
	public function getTeamIdentifierKey()
	{
		return 'team_id';
	}

	public function setPassword($password)
	{
		$this->password = $password;
	}
}