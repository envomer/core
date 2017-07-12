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

    public function add($name, $class)
    {
        if( $this->name && $this->name !== $name ) {
            return false;
        }

        if( is_string($class) ) {
            $class = new $class;
        }

        $this->apis[$name] = $class;
    }

    public function setApi($name = null)
    {
        $name = $name ?: $this->name;
        if( ! $name || ! isset($this->apis[$name]) ) {
            throw new InternalException('API name is not defined: ' . ($name));
        }

        $this->api = $this->apis[$name];

        $this->api->build();

        return $this->api;
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
			return $this->api->getAll($this->user, $input);
		}
		$per_page = isset($input['limit']) ? (int)$input['limit'] : 50;

	    $index = [];
	    if( method_exists($this->api, 'index') ) {
	    	$index = $this->api->index([],$this->user, $input);
	    }
	    if( is_string($index) || ( is_bool($index) && ! $index )) {
            return $index;
        }

	    $attributes = [];
		$attributes['columns'] = isset($index['columns']) ? (is_string($index['columns']) ? $index['columns'] : implode(',', $index['columns'])) : null;
	    $conditions = '';
	    $bind = [];

		if( $input ) {
			// search equals
			$indexEqual = isset($index['equal']) ? array_flip($index['equal']) : [];
			if( isset($input['fi']) && is_array($input['fi']) && ($this->user->isMod() || (!$this->user->isMod() && $indexEqual)) ) {
				foreach($input['fi'] as $key => $val) {
					if( $val === '' ) continue;
					if( $val === 'null' ) $val = null;
					if( ! $this->user->isMod() && ! isset($indexEqual[$key]) ) continue;

					if( strpos($val, ' ~ ') !== false && ($parts = explode(' ~ ', $val)) && count($parts) == 2 ) {
						$conditions .= (($conditions) ? ' AND ' : '') . 'STR_TO_DATE('.$key.',\'%Y-%m-%d\') BETWEEN :'.$key.'1: AND :'.$key.'2:';
						$bind[$key.'1'] = \Date::validate($parts[0]);
						$bind[$key.'2'] = \Date::validate($parts[1]);
					}
					else if( ($date = \Date::validate($val)) ) {
						$bind[$key] = $date;
						$conditions .= (($conditions) ? ' AND ' : '') . 'STR_TO_DATE('.$key.',\'%Y-%m-%d\') = :' .$key . ':';
					}
					else {
						$conditions .= (($conditions) ? ' AND ' : '') . $key . ' = :' .$key . ':';
						$bind[$key] = $val;
					}
				}
			}

			//  search like
			$indexLike = isset($index['like']) ? array_flip($index['like']) : [];
			if( isset($input['si']) && ($this->user->isMod() || (!$this->user->isMod() && $indexLike)) ) {
				if( is_array($input['si']) ) {
					foreach($input['si'] as $key => $val) {
						if( ! $this->user->isMod() && ! isset($indexLike[$key]) ) continue;
						$conditions .= (($conditions) ? ' OR ' : '') . $key . ' LIKE :' .$key . ':';
						$bind[$key] = $val;
					}
				} else {
					$i = 0;
					foreach($indexLike as $key => $k) {
						$conditions .= (($conditions) ? ' OR ' : '') . $key . ' LIKE :search'.$i.':';
						if( strpos($input['si'], '%') !== false ) {
							$bind['search' . $i] = $input['si'];
						} else {
							$bind['search' . $i] = '%'. $input['si'] . '%';
						}
						$i++;
					}
				}
			}
			if( isset($input['per_page']) && $input['per_page'] > 0 && $input['per_page'] <= 500 ) {
				$per_page = (int)$input['per_page'];
			}
		}

		$limits = [];
		$attributes['offset'] = $limits['offset'] = ($per_page * ($page - 1));
		$attributes['limit'] = $limits['limit'] = $per_page;

		if( isset($index['conditions']) ) $attributes['conditions'] = $index['conditions'];
		if( isset($index['bind']) ) $attributes['bind'] = $index['bind'];
		if( isset($index['join']) ) $attributes['join'] = $index['join'];
		if( isset($index['order']) ) $attributes['order'] = $index['order'];
		if( $conditions ) $attributes['conditions'] = ((isset($index['conditions'])) ? $attributes['conditions'] . ' AND ' : '') . '(' . $conditions . ')';
		if( $bind ) $attributes['bind'] = $bind;
		if( isset($index['bind']) ) $attributes['bind'] = array_merge($attributes['bind'], $index['bind']);

		if( $index ) {
			$this->api->index($attributes, $this->user, $input);
		}

		if( isset($attributes['bind']) && ! isset($attributes['conditions']) && ! isset($index['query']) ) {
			throw new InternalException("Set a condition", 500);
		}

		if( ! $attributes['columns'] ) {
			unset($attributes['columns']);
		}

	    if( isset($index['with']) && is_string($index['with']) && $index['with'] ) {
			$index['with'] = explode(',', $index['with']);
		}

	    if( isset($index['with']) && ! $index['with'] ) {
			$index['with'] = array();
		}

		if( isset($input['with']) && $input['with'] ) {
			// TODO: check if input are allowed
			if( is_string($input['with']) ) {
				$input['with'] = explode(',', $input['with']);
			}
			$index['with'] = array_merge($index['with'], $input['with']);
		}

	    // The data set to paginate
	    if( (isset($index['with']) && $index['with']) || isset($index['query']) ) {
    		/**
    		 * Simpler get relations version
    		 */
	    	if( isset($index['query']) )  {
	    		$bind = isset($index['bind']) ? $index['bind'] : null;
				$queryBind = $bind;
	    		if( strpos($index['query'], ':limit:') ) {
					$queryBind['limit'] = $limits['limit'];
				}
	    		if( strpos($index['query'], ':offset:') ) {
					$queryBind['offset'] = $limits['offset'];
				}

	    		$robots = $this->api->modelsManager->executeQuery($index['query'], $queryBind);
	    		$counts = $this->api->modelsManager->executeQuery($index['count'], $bind);
	    		$countTotal = 0;
	    		if ( $counts && isset($counts[0]) ) {
	    			$countTotal = $counts[0]->cnt;
	    		}
	    	}
	    	else {
	    		$robots = $this->api->find($attributes);
	    	}

			if( (isset($index['with']) && $index['with']) ) {
				$robots = \Lazyload::fromResultset($robots, $index['with']);

				/**
				* Hot fix for displaying relations (json formatted)
				* @var array
				*/
				$arr = [];
				if( ! $robots ) $robots = array();
				foreach($robots as $robot) {
					$mod = $robot->toArray();
					foreach ($index['with'] as $k => $val) {
						if( is_object($val) ) {
							$val = $k;
						}
						$result = $robot->$val;
						if(!($result)) {
							continue;
						}
						if( !is_array($result) ) {
							$mod[$val] = $result->toArray();
							continue;
						}
						foreach($result as $cit) {
							$mod[$val][] = $cit->toArray();
						}
					}
					$arr[] = $mod;
				}

				$robots = $arr;
			}
	    }
		else {
	    	$attributes['limit'] = $limits['limit'];
	    	$attributes['offset'] = $limits['offset'];
	    	$robots = $this->api->model->find($attributes);
	    	if( $robots ) {
				$robots = $robots->toArray();
			}
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
		
	    $pages = new Paginator($robots, $countTotal, (int)$page, (int)$per_page);

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

		return $entry;
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

		if( ! $this->api->model->save() ) {
			throw new PublicException(\_t('api.unableToCreateEntity'), 400);
		}
		
		$this->hook('postPersist');
		$this->hook('postCreate');

		return true;
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
		$conditions = $this->identifier .' = :val:';
		if( $this->api->isSoftDeletable() ) {
			$conditions .= ' AND deleted_at IS NULL';
		}
		$bind = ['val' => $entry_id];
		$with = [];

		if( $isApi && method_exists($this->api, 'show') ) {
			$show = $this->api->show($this->user, $data);
			$dataWith = isset($data['with']) ? $data['with'] : array();
			if( ! is_array($dataWith) ) {
				$dataWith = explode(',', $dataWith);
			}
			if( array_key_exists('conditions', $show) && $show['conditions'] ) {
				$conditions .= ' AND ' . $show['conditions'];
			}
			if( array_key_exists('bind', $show) && is_array($show['bind']) ) {
				$bind = array_merge($bind, $show['bind']);
			}
			if( array_key_exists('with', $show) ) {
				$with = array_merge($with, $show['with'], $dataWith);
			}
		}

		$find = [
				'conditions' => $conditions,
				'bind' => $bind,
		];
		
		$result = $this->api->findFirst($find);
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

}