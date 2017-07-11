<?php

namespace Envo;

class AbstractAPI
{
    public $model = null;
    public $name = null;

    public function __construct()
    {
        $this->init();
    }

    public function getName()
    {
        if( $this->name ) {
            return $this->name;
        }

        return  $this->name = basename(str_replace('\\', '/', get_called_class()));
    }
}