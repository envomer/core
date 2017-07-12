<?php

namespace Envo;

use Envo\Exception\InternalException;

class AbstractAPI
{
    public $model = null;
    public $dto = null;
    public $name = null;
    public $user = null;

    public function build()
    {
        if( method_exists($this, 'init') ) {
            $this->init();
        }

        $this->buildModel();
        $this->buildDTO();
    }

    public function buildModel()
    {
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

    public function buildDTO()
    {
        if( ! $this->dto ) {
            $this->dto = str_replace('\API\\', '\DTO\\', get_called_class()) . 'DTO';
        }

        if( is_string($this->dto) ) {
            if( ! class_exists($this->dto) ) {
                throw new InternalException('DTO not found', 500);
            }
            
            $this->dto = new $this->dto;
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