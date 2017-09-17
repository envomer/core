<?php

namespace Envo\API;

use Envo\Exception\InternalException;
use Envo\Exception\PublicException;
use Envo\Support\Paginator;

class Handler
{
    public $apis = [];
    public $name = null;
    public $api = null;
	public $request;
	public $user = null;

	/**
	 * Add new api endpoint
	 *
	 * @param string $name
	 * @param string|AbstractAPI $class
	 * @return void
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
	 * @return void
	 */
    public function setApi($name = null)
    {
        $name = $name ?: $this->name;
        if( ! $name || ! isset($this->apis[$name]) ) {
			internal_exception('api.nameNotDefined', 404);
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
	 * @return \Paginator
	 * @throws \Exception
	 */
	public function getAll($page = 1, $input = null)
	{
		if( method_exists($this->api, 'getAll') ) {
			return $this->api->getAll();
		}

		$this->requestValidate();
		$limit = $this->api->request->limit;
		
		$builder = $this->api->model->getModelsManager()->createBuilder();
		$builder->from(['e' => \get_class($this->api->model)]);

	    $index = [];
	    if( method_exists($this->api, 'index') ) {
	    	$index = $this->api->index($builder);
	    }

		$builder->offset(($limit * ($page - 1)));
		$builder->limit($limit);

		$query = $builder->getQuery();
		$data = $query->execute();
		
		$config = $this->getApiConfig();
		
		if( $data ) {
			$data = $this->transform(
				$this->api->getConfig('index.transformation') == 'object' ? $data : $data->toArray()
			);
		}

	    if( !isset($countTotal) ) {
			$builder->columns('COUNT(*)');
			$builder->limit(null);
			$builder->offset(null);
			$counter = $builder->getQuery()->execute();
	    	$countTotal = $counter->getFirst()->{0};
	    }
		
	    $pages = new Paginator($data, $countTotal, (int)$page, (int)$limit);

	    return $pages;
	}
	
	/**
	 * Get database entry
	 *
	 * @param $entry_id
	 *
	 * @return string|array
	 */
	public function get($entry_id, $data = array())
	{
		$entry = $this->find($entry_id, true, $data);
		if ( ! $entry ) {
			return _t('app.notfound');
		}

		return [
			'data' => $this->transform($entry->toArray())
		];
	}
	
	/**
	 * Save new entry
	 * @todo recheck and run thorough tests.
	 * this method is a huge security risk
	 *
	 * @param $content
	 *
	 * @return bool
	 */
	public function save($content)
	{
		$this->hook('prePersist');
		$this->hook('preCreate');

		$validation = $this->hook('validate');
		if( is_array($validation) ) {
			$this->api->check($validation);
		}

		if( ! $this->api->model->save() ) {
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
	 * @param $entry_id
	 * @param $content
	 *
	 * @return bool
	 */
	public function update($entry_id, $content)
	{
		$entry = $this->find($entry_id, false);
		if( ! $entry ) {
			return _t('app.notfound');
		}

		$result = $entry->override($content, true, 'update');

		if( ! $result || is_string($result) || is_array($result) ) {
			return $result;
		}

		if( method_exists($entry, 'preSave') ) {
			$response = $entry->preSave();
			if( is_string($response) || (is_bool($response) && ! $response) ) {
				return $response;
			}
		}

		if( $entry->allowUpdate() && ! $entry->update() ) {
			if( $msgs = $entry->getMessages() ) {
				return $msgs;
			}
		    return false;
		}

		if( method_exists($entry, 'postSave') ) {
			// TODO: handle errors
			return $entry->postSave();
		}

		return true;
	}
	
	/**
	 * Delete entry
	 *
	 * @param      $entry_id
	 * @param bool $force
	 * @param null $data
	 *
	 * @return bool
	 */
	public function delete($entry_id, $force = false, $data = null)
	{
		/**
		 * TODO: disallow unauthorized users
		 * Make sure all models have a show method with a condition set
		 * SECURITY BREACH
		 */
		$entry = $this->find($entry_id, false, $data);
		if( ! $entry || ! $entry->isDeletable() ) {
			return _t('app.notfound');
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
		else {
			if( $entry->isSoftDeletable() ) {
				$entry->deleted_at = date('Y-m-d H:i:s');
				return $entry->save();
			}
			return $entry->delete();
		}
	}
	
	/**
	 * Restore an entry
	 *
	 * @param $entry_id
	 *
	 * @return bool
	 */
	public function restore($entry_id)
	{
		$entry = $this->api->onlyTrashed()->where($this->identifier, $entry_id)->first();
		if( ! $entry ) {
			return _t('app.notfound');
		}

		return $entry->restore();
	}
	
	/**
	 * Find model entity
	 *
	 * @param      $entry_id
	 * @param bool $isApi
	 *
	 * @return \AbstractModel
	 */
	public function find($entry_id, $isApi = true, $data = array())
	{
		$conditions = $this->api->identifier .' = :val:';
		if( $this->api->model->isSoftDeletable() ) {
			$conditions .= ' AND deleted_at IS NULL';
		}
		$bind = ['val' => $entry_id];
		$with = [];

		if( $isApi && method_exists($this->api, 'show') ) {
			$show = $this->api->show($this->user, $data);
		}

		$find = [
			'conditions' => $conditions,
			'bind' => $bind,
		];
		
		$result = $this->api->model->findFirst($find);
		if( $result && $with ) {
			$result->load($with);
		}

		if( $result && method_exists($result, 'onShow') ) {
			$result->onShow();
		}

		return $result;
	}

	/**
	 * Trigger hook
	 *
	 * @param [type] $name
	 * @return void
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
	 * @return void
	 */
	public function transform($data)
	{
		$definition = null;

		if( method_exists($this->api, 'transformDefinition') ) {
			$definition = $this->api->transformDefinition($data);
		}

		if( 
			($apiTransformation = method_exists($this->api, 'transformItem')) || 
			$this->request->method == 'index'
		) {
			$context = $apiTransformation ? $this->api : $this;

			return array_map(function($item) use($definition, $context) {
				return $context->transformItem($item, $definition);
			}, $data);
		}
		else if( $definition ) {
			return $this->transformItem($data, $definition);
		}

		// if( 
		// 	method_exists($this->api, 'transform') 
		// 	&& ($transform = $this->api->transform($data)) 
		// 	&& is_array($transform) 
		// ) {
		// 	$definition = array_flip($transform);
			
		// 	return $this->transformItem($data, $definition);
		// }

		// if( $this->request->method === 'index' || method_exists($this->api, 'transformItem') ) {
		// 	return array_map(function($item) use($definition) {
		// 		return $this->transformItem($item, $definition);
		// 	}, $data);
		// }
			

		return $data;
	}

	/**
	 * Transform on item
	 *
	 * @param array|object $data
	 * @param array $definition
	 * @return void
	 */
	public function transformItem($data, $definition)
	{
		if( is_object($data) ) {
			$data = (array) $data;
		}

		if( is_array($data) ) {
			return array_intersect_key($data, $definition);
		}
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