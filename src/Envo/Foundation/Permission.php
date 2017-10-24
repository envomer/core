<?php

namespace Envo\Foundation;

class Permission
{
    /**
     * This attribute holds all available permissions
     * (see: /config/permission.php)
     *
     * @var array
     */
    public $permissions = [];

    /**
     * Get permissions from config file
     */
    public function __construct()
    {
        $this->permissions = require(APP_PATH . 'config/permissions.php');
    }

    public function can($user, $key)
    {
        if( $user->isAdmin() ) {
            return true;
        }

        list($module, $name) = $this->parseKey($key);
        
        if( $user->isOwner() && in_array($module, $this->permissions['open']) ) {
            return true;
        }

        return isset($this->getKeysByUser($user)[$key]);
    }

    public function parseKey($key)
    {
        $parts = explode('.', $key);

        if( count($parts) > 1 ) {
            return $parts;
        }
        
        return [$key, null];
    }

    public static function getInstance()
    {
        return \Phalcon\DI::getDefault()->get('permission');
    }

    public function getAllKeys()
    {
        $keys = [];

        foreach($this->permissions['modules'] as $module => $permissions) {
            foreach($permissions as $permission) {
                $keys[] = $module . '.' . $permission;
            }
        }

        return $keys;
    }

    public function getKeysByUser($user)
    {
        $keysAll = $this->getAllKeys();

        if( ! $user->isAdmin() ) {
            $permissions = \Arr::reference($user->getPermissions(), 'name');
            $userKeys = array_keys($permissions);
            $userKeys = array_merge($userKeys, $this->permissions['open']);

            if( $user->isOwner() ) {
                $userKeys = $this->setOpenKeysForOwners($userKeys);
            }
        }
        else {
            $userKeys = $keysAll;
        }

        return $userKeys;
    }

    public function setOpenKeysForOwners($userKeys)
    {
        $restrictedModuleKeys = array_flip($this->getRestrictedKeys());

        foreach($userKeys as $key) {
            if( isset($this->permissions['modules'][$key]) ) {
                foreach($this->permissions['modules'][$key] as $module => $moduleKey) {
                    if( ! in_array($moduleKey, $userKeys) && ! isset($restrictedModuleKeys[$key . '.' . $moduleKey]) ) {
                        $userKeys[] = $key . '.' . $moduleKey;
                    }
                }
            }
        }

        return $userKeys;
    }

    public function getPublicKey($user)
    {
        $keys = $this->getKeysByUser($user);
        // die(var_dump($user));

        // TODO:
        
        $publicKey = [];
        foreach($keys as $key) {
            list($module, $name) = $this->parseKey($key);

            if( ! isset($publicKey[$module]) ) {
                $publicKey[$module] = $module;
            }
            
            if( ! $name ) {
                continue;
            }

            $publicKey[$module] .= $this->convertKey($name);
        }
        
        return strtoupper(implode('.', $publicKey));
    }

    public function convertKey($key)
    {
        return substr($key, 0, 1) . substr($key, -1) . substr($key, -2, 1) . strlen($key);
    }

    public function getRestrictedKeys()
    {
        return $this->permissions['restricted'];
    }

    public function getOpenModuleKeys()
    {
        $keys = [];
        foreach($this->getAllKeys() as $moduleKey) {
            if( ! in_array($moduleKey, $this->permissions['restricted']) ) {
                $keys[] = $moduleKey;
            }
        }

        return $keys;
    }

    public function groupPermissions($permissions, $extractModule = false)
    {
        $grouped = [];
        $modules = \Config::get('modules');
        foreach($permissions as $permission) {
            list($module, $name) = $this->parseKey($permission);

            if( ! isset($grouped[$module]) ) {
                $grouped[$module] = [
                    'name' => $module,
                    'display' => isset($modules[$module]) ? $modules[$module] : $module,
                    'permissions' => []
                ];
            }

            if( ! $name ) {
                continue;
            }

            $grouped[$module]['permissions'][] = $extractModule ? $name : $permission;
        }

        return $grouped;
    }
}