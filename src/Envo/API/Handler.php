<?php

namespace Envo\API;

use Envo\AbstractAPI;
use Envo\AbstractModel;
use Envo\Exception\InternalException;
use Envo\Exception\PublicException;
use Envo\Support\Paginator;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset;

class Handler
{
    public $apis = [];
    public $name;
	
	/**
	 * @var AbstractAPI
	 */
    public $api;
	public $request;
	public $user;
	
	/**
	 * Add new api endpoint
	 *
	 * @param string             $name
	 * @param string|AbstractAPI $class
	 *
	 * @return bool
	 */
    public function add($name, $class)
    {
        if( $this->name && $this->name !== $name ) {
            return false;
        }

        $this->apis[$name] = $class;
    }
	
	/**
	 * Set api instance
	 *
	 * @param string $name
	 *
	 * @return AbstractAPI
	 * @throws InternalException
	 */
    public function setApi($name = null)
    {
        $name = $name ?: $this->name;
        if( ! $name || ! isset($this->apis[$name]) ) {
			internal_exception('api.nameNotDefined', 404, [
				'name' => $name
			]);
        }

        $this->api = $this->apis[$name];

        if( is_string($this->api) ) {
        	$class = $this->api;
            $this->api = new $class;
            $this->api->name = $name;
        }

		$this->api->request = $this->request;
		$this->api->user = $this->user;
        $this->api->build();

        return $this->api;
    }

	/**
	 * Validate the api request
	 *
	 * @return void
	 */
	public function requestValidate()
	{
		$request = $this->api->request;

		$request->limit = (int) $request->limit ?: 50;
		if( $request->limit < 0 || $request->limit > 500 ) {
			$request->limit = 50;
		}

		$request->page = (int) $request->page ?: 1;
		if( $request->page < 0 ) {
			$request->page = 50;
		}
	}
	
	/**
	 * Get all entries
	 *
	 * @param int  $page
	 * @param null $input
	 *
	 * @return Paginator
	 */
	public function getAll($page = 1, $input = null)
	{
		if( method_exists($this->api, 'getAll') ) {
			return $this->api->getAll();
		}

		$this->requestValidate();
		$limit = $this->api->request->limit;
		$manipulator = null;
		
		$builder = $this->api->model->createBuilder();
		
	    if( method_exists($this->api, 'index') ) {
	    	/** @var mixed $manipulator */
	    	$manipulator = $this->api->index($builder);
	    }

		$builder->offset($limit * ($page - 1));
		$builder->limit($limit);

		/** @var \Phalcon\Mvc\Model\Query $query */
		$query = $builder->getQuery();
		/** @var \Phalcon\Mvc\Model\Resultset\Simple $data */
		$data = $query->execute();
		
		if( $manipulator ) {
			$data = $manipulator($data);
		}
		
		$data = $this->hydrateResultItems($data, 'index.hydrate');
		
		if( $data ) {
			$data = $this->transform($data);
		}

		$builder->columns('COUNT(*)');
		$builder->limit(null);
		$builder->offset(null);
		$counter = $builder->getQuery()->execute();
		$countTotal = $counter->getFirst()->{0};
		
		return new Paginator($data, $countTotal, (int)$page, (int)$limit);
	}
	
	/**
	 * @param \Phalcon\Mvc\Model\Resultset\Simple $data
	 * @param $configKey
	 *
	 * @return mixed
	 */
	protected function hydrateResultItems($data, $configKey)
	{
		if(!method_exists($data, 'setHydrateMode')) {
			return $data;
		}

		$mode = $this->api->getConfig($configKey, AbstractAPI::HYDRATE_MODEL);
		
		switch ($mode) {
			case AbstractAPI::HYDRATE_ARRAY:
				return $data->toArray();
			case AbstractAPI::HYDRATE_MODEL:
				return $data->setHydrateMode(Resultset::HYDRATE_RECORDS);
			case AbstractAPI::HYDRATE_OBJECT:
				return $data->setHydrateMode(Resultset::HYDRATE_OBJECTS);
		}
	}
	
	/**
	 * Get database entry
	 *
	 * @param       $entityId
	 *
	 * @param array $data
	 *
	 * @return string|array
	 * @throws PublicException
	 */
	public function get($entityId, array $data = [])
	{
		if( method_exists($this->api, 'get') ) {
			return $this->api->get();
		}

		$entity = $this->find($entityId, true, $data);
		if ( ! $entity ) {
			public_exception('api.entityNotFound', 404);
		}

		return [
			'data' => $this->transform(
				//$this->api->getConfig('show.hydrate') === AbstractAPI::HYDRATE_MODEL ? $entity : $entity->toArray()
				$this->hydrateResultItems($entity, 'show.hydrate')
			)
		];
	}
	
	/**
	 * Save new entry
	 * @todo recheck and run thorough tests.
	 * this method is a huge security risk
	 *
	 * @return array
	 * @throws PublicException
	 */
	public function save()
	{
		$validation = $this->hook('validate');
		if( is_array($validation) ) {
			$this->api->check($validation);
		}
		
		$this->hook('prePersist');
		$response = $this->hook('preCreate');
		
		if( $response !== $this->api::SAVE_SKIP && ! $this->api->model->save() ) {
			public_exception('api.failedToCreateEntity', 400, $this->api->model);
		}
		
		$this->hook('postPersist');
		$this->hook('postCreate');

		return [
			'data' => $this->transform($this->api->model)
		];
	}
	
	/**
	 * Update new entry
	 * @todo recheck and run thorough tests.
	 * this method is a huge security risk
	 *
	 * @param $entityId
	 *
	 * @return array
	 * @throws PublicException
	 */
	public function update($entityId)
	{
		$entry = $this->find($entityId, false);
		if( ! $entry ) {
			public_exception('api.entityNotFound', 404);
		}
		
		$this->api->model = $entry;
		$validation = $this->hook('validate');
		if( is_array($validation) ) {
			$this->api->check($validation);
		}
		
		$this->hook('prePersist');
		$this->hook('preUpdate');
		
		if( ! $this->api->model->save() ) {
			public_exception('api.failedToUpdateEntity', 400, $this->api->model);
		}
		
		$this->hook('postPersist');
		$this->hook('postUpdate');
		
		return [
			'data' => $this->transform($this->api->model)
		];
	}
	
	/**
	 * Delete entry
	 *
	 * @param      $entityId
	 * @param bool $force
	 * @param null $data
	 *
	 * @return bool
	 */
	public function delete($entityId, $force = false, $data = null)
	{
		/**
		 * TODO: disallow unauthorized users
		 * Make sure all models have a show method with a condition set
		 * SECURITY BREACH
		 */
		$entry = $this->find($entityId);
		if( ! $entry || ! $entry->isDeletable() ) {
			return _t('app.notFound');
		}

		if( method_exists($entry, 'preDelete') ) {
			$response = $entry->preDelete();
			if( is_string($response) || (is_bool($response) && ! $response) ) {
				return $response;
			}
		}
		
		if( ! $entry->allowUpdate() ) {
			return true;
		}
		
		if( $force ) {
			return $entry->delete();
		}
		
		if( $entry->isSoftDeletable() ) {
			$entry->deleted_at = date('Y-m-d H:i:s');
			return $entry->save();
		}
		return $entry->delete();
	}
	
	/**
	 * Restore an entry
	 *
	 * @param $entityId
	 *
	 * @return bool
	 */
	public function restore($entityId)
	{
		$entry = $this->api->onlyTrashed()->where($this->api->identifier, $entityId)->first();
		if( ! $entry ) {
			return _t('app.notfound');
		}

		return $entry->restore();
	}
	
	/**
	 * Find model entity
	 *
	 * @param mixed $entityId
	 *
	 * @return AbstractModel
	 */
	public function find($entityId)
	{
		$builder = $this->api->model->createBuilder();
		
		if( $this->api->request->method === 'show' && method_exists($this->api, 'show') ) {
			$this->api->show($builder, $entityId);
		}
		else {
			$className = get_class($this->api->model);
			$alias = strtolower(substr($className, strrpos($className, '\\') + 1)[0]);
			$builder->where($alias. '.'.$this->api->identifier . ' = :val:', [
				'val' => $entityId
			]);
		}

		if( $this->api->model->isSoftDeletable() ) {
			$builder->andWhere('e.deleted_at IS NULL');
		}

		$query = $builder->getQuery();
		
		return $query->getSingleResult();
	}

	/**
	 * Trigger hook
	 *
	 * @param [type] $name
	 * @return mixed
	 */
	public function hook($name)
	{
		if( method_exists($this->api, $name) ) {
			return $this->api->$name();
		}
	}
	
	/**
	 * Transform api response
	 *
	 * @param [type] $data
	 *
	 * @return array
	 */
	public function transform($data)
	{
		$definition = null;

		if( method_exists($this->api, 'transform') ) {
			$data = $this->api->transform($data);
		}

		if( method_exists($this->api, 'transformDefinition') ) {
			$definition = $this->api->transformDefinition($data);
		}

		$apiTransformation = method_exists($this->api, 'transformItem');
		$context = $apiTransformation ? $this->api : $this;
		$definition = $definition ? array_flip($definition) : null;

		if( 
			$this->request->method === 'index' && ($apiTransformation || $definition)
		) {
			$result = [];
			foreach ($data as $item) {
				$result[] = $context->transformItem($item, $definition);
			}
			return $result;
			//return array_map(function($item) use($definition, $context) {
			//	return $context->transformItem($item, $definition);
			//}, $data);
		}
		
		if( $apiTransformation || $definition ) {
			return $context->transformItem($data, $definition);
		}

		return $data;
	}

	/**
	 * Transform on item
	 *
	 * @param array|\stdClass $data
	 * @param array $definition
	 * @return array
	 */
	public function transformItem($data, $definition)
	{
		if(!$definition) {
			return $data;
		}
		
		if(is_a($data, Model::class)) {
			$data = $data->toArray();
		}
		
		if( is_object($data) ) {
			$data = (array) $data;
		}
		
		if( is_array($data) && $definition ) {
			return array_intersect_key($data, $definition);
		}
		
		return $data;
	}

	/**
	 * Authorize before handling request
	 *
	 * @return boolean
	 */
	public function isAuthorized()
	{
		if( $this->api && method_exists($this->api, 'authorize') ) {
			return $this->api->authorize();
		}

		return true;
	}

	/**
	 * Get configuration of api
	 *
	 * @return array
	 */
	public function getApiConfig()
	{
		if( method_exists($this->api, 'config') ) {
			return $this->api->config();
		}

		return [];
	}
}