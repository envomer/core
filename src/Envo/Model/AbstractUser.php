<?php

namespace Envo\Model;

use Envo\Support\Translator;

class AbstractUser extends AbstractLegalEntity
{
    const ACCESS_API_TOKEN = 1;
    const ACCESS_SESSION = 2;

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
    protected $accessMode = null;

    /**
     * Are softdeletes allowed?
     *
     * @var boolean
     */
    protected $softDeletes = true;

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
    public $identifier = null;

    /**
     * Username
     * 
     * @var string
     */
    public $username;

    /**
     * Is admin flag
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return false;
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
     * @return void
     */
    public function getTeamId()
    {
        return $this->team_id;
    }

    /**
     * Get identifier
     *
     * @return void
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Get api key
     *
     * @return void
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    public function can($permissionKey)
    {
        if($this->level !== null && $this->level === 9) {
            return true;
        }

        return $this->di->get('permission')->can($this, $name);
    }

    public function getPermissions()
	{
		if( isset($this->cachedRelations['permissions']) ) {
			return $this->cachedRelations['permissions'];
		}

		return $this->cachedRelations['permissions'] = $this->di->get('permission')->getByUserId($this->getId()) ?: [];
	}

	public function getPermissionPublicKey()
	{
		return $this->di->get('permission')->getPublicKey($this);
	}

	public function getPermissionKeys()
	{
		return $this->di->get('permission')->getKeysByUser($this);
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
	 * @param LegalEntity $legalEntity
	 *
	 * @return AbstractLegalEntity
	 */
	public function setParent( LegalEntity $legalEntity )
	{
		$this->parent = $legalEntity;
		
		return $this;
	}
}