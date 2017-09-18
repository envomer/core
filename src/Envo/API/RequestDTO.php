<?php

namespace Envo\API;

use Envo\AbstractDTO;

class RequestDTO extends AbstractDTO
{
    public $parameters = null;

    public $limit;

    public $page;

    public $method;

    public $id;

    public function get($name, $default = null)
    {
        if(array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        }

        if( isset($this->$name) ) {
            return $this->$name;
        }

        return $default;
    }
}