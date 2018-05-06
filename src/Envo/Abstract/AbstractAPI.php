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
	const HYDRATE_OBJECT = 1;
	const HYDRATE_MODEL = 2;
	const HYDRATE_ARRAY = 3;
    const SAVE_SKIP = 'save.skip';
	
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
        
        if(method_exists($this, 'config')) {
        	$this->config = $this->config();
		}
    }
	
	/**
	 * @return $this
	 */
    public function buildModel()
    {
        $modelClass = $this->model;
        if( ! $this->model ) {
        	// TODO refactor
            $modelClass = str_replace('\API\\', '\Model\\', static::class);
            $classParts = explode('\\', $modelClass);
            $length = count($classParts);
            $classParts[$length - 1] = str_replace('API', '', $classParts[$length - 1]);
            $modelClass = implode('\\', $classParts);
        }

        if( is_string($modelClass) ) {
            if( ! class_exists($modelClass) ) {
                return null;
                // internal_exception('api.modelNotFound', 404);
            }
            
            $this->model = new $modelClass;
        }
		
		return $this;
    }
	
	/**
	 * @return null
	 */
    public function buildDTO()
    {
    	$dto = $this->dto;
        if( ! $dto ) {
			// TODO refactor
			$dto = str_replace('\API\\', '\DTO\\', static::class);
			$classParts = explode('\\', $dto);
			$length = count($classParts);
			$classParts[$length - 1] = str_replace('API', '', $classParts[$length - 1]);
			$dto = implode('\\', $classParts) .'DTO';
        }

        if( is_string($dto) ) {
			if( ! class_exists($dto, true) ) {
				return null;
			}

            $data = null;
            if( $this->request && isset($this->request->parameters['data']) ) {
                $data = $this->request->parameters['data'];
            }


            $this->dto = new $dto($data);
        }
		
		return $this;
    }
	
	/**
	 * @return self
	 */
    public function buildRepo()
    {
        if( ! $this->repo ) {
            // $this->repo = str_replace('\API\\', '\Repository\\', static::class) . 'Repository';
            // TODO refactor
            $dto = str_replace('\API\\', '\Repository\\', static::class);
            $classParts = explode('\\', $dto);
            $length = count($classParts);
            $classParts[$length - 1] = str_replace('API', '', $classParts[$length - 1]);
            $this->repo = implode('\\', $classParts) .'Repository';
        }

        if( is_string($this->repo) && $this->model ) {
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