<?php

namespace Envo\API;

class Handler
{
    public $apis = [];
    public $model = null;

    public function add($name, $class)
    {
        if( $model && $model !== $name ) {
            return false;
        }

        if( is_string($class) ) {
            $class = new $class;
        }

        $apis[$class->getName()] = $class;
    }

    public function getApi()
    {
        
    }

}