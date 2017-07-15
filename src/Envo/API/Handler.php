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

    public function add($name, $class)
    {
        if( $this->name && $this->name !== $name ) {
            return false;
        }

        if( is_string($class) ) {
            $class = new $class;
        }

		$class->name = $name;

        $this->apis[$name] = $class;
    }

    public function setApi($name = null)
    {
        $name = $name ?: $this->name;
        if( ! $name || ! isset($this->apis[$name]) ) {
			internal_exception('api.nameNotDefined', 404);
        }

        $this->api = $this->apis[$name];

		$this->api->request = $this->request;
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

	    $index = [];
	    if( method_exists($this->api, 'index') ) {
	    	$index = $this->api->index();
	    }

	    if( is_string($index) || ( is_bool($index) && ! $index )) {
            return $index;
        }

	    $attributes = [];
		$attributes['offset'] = $limits['offset'] = ($limit * ($page - 1));
		$attributes['limit'] = $limits['limit'] = $limit;

		$robots = $this->api->model->find($attributes);
		if( $robots ) {
			$robots = $this->transform($robots->toArray());
		}

	    if( !isset($countTotal) ) {
		    if(isset($attributes['limit'])) {
				unset($attributes['limit']);
			}
		    if(isset($attributes['offset'])) {
				unset($attributes['offset']);
			}
	    	$countTotal = $this->api->model->count($attributes);
	    }
		
	    $pages = new Paginator($robots, $countTotal, (int)$page, (int)$limit);

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

		$this->hook('validate');

		if( ! $this->api->model->save() ) {
			public_exception('api.failedToCreateEntity', 400, $this->api->model);
		}
		
		$this->hook('postPersist');
		$this->hook('postCreate');

		return [
			'data' => [
				$this->api->getName() => $this->api->dto
			]
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
			$response = $entry->preSave(\Auth::user(), $content);
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
			return $entry->postSave(\Auth::user(), $content);
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
			$response = $entry->preDelete(\Auth::user(), $data);
			if( is_string($response) || (is_bool($response) && ! $response) ) {
				return $response;
			}
		}

		if( isset($data['with']) ) {
			$with = is_array($data['with']) ? $data['with'] : explode(',', $data['with']);
			foreach($with as $item) {
				$entry->$item->delete();
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
	 * find model
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

	public function hook($name)
	{
		if( method_exists($this->api, $name) ) {
			$this->api->$name();
		}
	}

	public function transform($data)
	{
		if( method_exists($this->api, 'transform') && ($transform = $this->api->transform()) && is_array($transform) ) {
			$definition = array_flip($transform);

			if( $this->api->request->method === 'show' ) {
				return array_intersect_key($data, $definition);
			}

			return array_map(function($item) use($definition) {
				return array_intersect_key($item, $definition);
			}, $data);
		}

		return $data;
	}

}