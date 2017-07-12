<?php

namespace Envo;

use Envo\Exception\InternalException;

class AbstractAPI
{
    public $model = null;
    public $name = null;

    public function build()
    {
        if( method_exists($this, 'init') ) {
            $this->init();
        }

        if( ! $this->model ) {
            $this->model = str_replace('\API\\', '\Model\\', get_called_class());
        }

        if( is_string($this->model) ) {
            if( ! class_exists($this->model) ) {
                throw new InternalException('Model not found', 500);
            }
            
            $this->model = new $this->model;
        }
    }

    public function getName()
    {
        if( $this->name ) {
            return $this->name;
        }

        return $this->name = basename(str_replace('\\', '/', get_called_class()));
    }
}