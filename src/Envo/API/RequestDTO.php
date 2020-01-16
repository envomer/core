<?php

namespace Envo\API;

use Envo\AbstractDTO;

class RequestDTO extends AbstractDTO
{
    const REQUEST_METHOD_INDEX = 'index';
    const REQUEST_METHOD_SHOW = 'show';
    const REQUEST_METHOD_CREATE = 'store';
    const REQUEST_METHOD_UPDATE = 'update';
    
    public $parameters = null;

    public $limit;

    public $page;

    public $method;

    public $id;

    public $headers;

    public function get($name = null, $default = null)
    {
        if(!$name) {
            return $this->parameters;
        }

        if(array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        }

        if( isset($this->$name) ) {
            return $this->$name;
        }

        return $default;
    }
}
