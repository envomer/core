<?php

namespace Envo;

use Envo\Exception\InternalException;
use Envo\API\RequestDTO;
use Envo\Support\Validator;

class AbstractAPI
{
    public $model = null;
    public $dto = null;
    public $name = null;
    public $user = null;
    public $repo = null;

    public $config = null;
    
    public $identifier = 'id';
	
	/**
	 * @var RequestDTO $request
	 */
    public $request = null;
	
	/**
	 * Build API class (DTO/mode/Repo)
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

    public function buildModel()
    {
        if( ! $this->model ) {
            $this->model = str_replace('\API\\', '\Model\\', get_called_class());
        }

        if( is_string($this->model) ) {
            if( ! class_exists($this->model) ) {
                internal_exception('api.modelNotFound', 404);
            }
            
            $this->model = new $this->model;
        }
    }

    public function buildDTO()
    {
        if( ! $this->dto ) {
            $this->dto = str_replace('\API\\', '\DTO\\', get_called_class()) . 'DTO';
        }

        if( is_string($this->dto) ) {
            if( ! class_exists($this->dto) ) {
                internal_exception('api.dtoNotFound', 404);
            }

            $data = null;
            if( $this->request && isset($this->request->parameters[$this->getName()]) ) {
                $data = $this->request->parameters[$this->getName()];
            }

            $this->dto = new $this->dto($data);
        }
    }

    public function buildRepo()
    {
        if( ! $this->repo ) {
            $this->repo = str_replace('\API\\', '\Repository\\', get_called_class()) . 'Repository';
        }

        if( is_string($this->repo) ) {
            if( ! class_exists($this->repo) ) {
                return $this->repo = new AbstractRepository($this->model);
            }

            $this->repo = new $this->repo($this->model);
        }
    }
	
	/**
	 * @return null|string
	 */
    public function getName()
    {
        if( $this->name ) {
            return $this->name;
        }

        return $this->name = strtolower(basename(str_replace('\\', '/', get_called_class())));
    }
	
	/**
	 * @param $validations
	 *
	 * @return bool
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