<?php

namespace Envo;

use Envo\API\RequestDTO;
use Envo\Model\User;
use Envo\Support\Validator;

/**
 * Class AbstractAPI
 *
 * @package Envo
 */
abstract class AbstractAPI
{
	/**
	 * @var AbstractModel
	 */
    public $model;
	
	/**
	 * @var AbstractDTO
	 */
    public $dto;
	
	/**
	 * @var string
	 */
    public $name;
	
	/**
	 * @var User
	 */
    public $user;
	
	/**
	 * @var AbstractRepository
	 */
    public $repo;
	
	/**
	 * ???
	 * @TODO what is this attribute for?
	 *
	 * @var array
	 */
    public $config;
	
	/**
	 * @var string
	 */
    public $identifier = 'id';
	
	/**
	 * @var RequestDTO $request
	 */
    public $request;
	
	/**
	 * Build API class (DTO/mode/Repo)
	 * @throws Exception\InternalException
	 */
    public function build()
    {
        if( method_exists($this, 'init') ) {
            $this->init();
        }

        $this->buildModel();
        $this->buildDTO();
        $this->buildRepo();
    }
	
	/**
	 * @return $this
	 * @throws Exception\InternalException
	 */
    public function buildModel()
    {
        if( ! $this->model ) {
            $this->model = str_replace('\API\\', '\Model\\', static::class);
        }

        if( is_string($this->model) ) {
            if( ! class_exists($this->model) ) {
                internal_exception('api.modelNotFound', 404);
            }
            
            $this->model = new $this->model;
        }
		
		return $this;
    }
	
	/**
	 * @return $this|bool
	 */
    public function buildDTO()
    {
        if( ! $this->dto ) {
            $this->dto = str_replace('\API\\', '\DTO\\', static::class) . 'DTO';
        }

        if( is_string($this->dto) ) {
            if( ! class_exists($this->dto) ) {
                return false;
            }

            $data = null;
            if( $this->request && isset($this->request->parameters['data']) ) {
                $data = $this->request->parameters['data'];
            }

            $this->dto = new $this->dto($data);
        }
		
		return $this;
    }
	
	/**
	 * @return self
	 */
    public function buildRepo()
    {
        if( ! $this->repo ) {
            $this->repo = str_replace('\API\\', '\Repository\\', static::class) . 'Repository';
        }

        if( is_string($this->repo) ) {
            if( ! class_exists($this->repo) ) {
                $this->repo = new AbstractRepository($this->model);
            } else {
				$this->repo = new $this->repo($this->model);
			}
        }
		
		return $this;
    }
	
	/**
	 * @return null|string
	 */
    public function getName()
    {
        if( $this->name ) {
            return $this->name;
        }

        return $this->name = strtolower(basename(str_replace('\\', '/', static::class)));
    }
	
	/**
	 * @param $validations
	 *
	 * @return bool
	 * @throws Exception\PublicException
	 */
    public function check($validations)
    {
    	/** @var Validator $validator */
        $validator = Validator::make($this->dto, $validations);

        if( $validator->fails() ) {
            public_exception('validation.failed', 400, $validator);
        }

        return true;
    }

    /**
     * Get config
     *
     * @param string $key
     * @param mixed $default
	 *
     * @return mixed
     */
    public function getConfig($key, $default = null)
    {        
        if( $this->config && array_key_exists($key, $this->config) ) {
            return $this->config[$key];
        }

        return $default;
    }
}