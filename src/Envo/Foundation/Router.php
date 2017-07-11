<?php

namespace Envo\Foundation;

class Router extends \Phalcon\Mvc\Router
{
    private static $router;

    public static function getInstance()
    {
        if( self::$router ) {
            return self::$router;
        }
        return self::$router = new self();
    }

    public function get($name, $path)
    {
        $this->add($name, $path);
    }

    public function api()
    {
        $api = new \Phalcon\Mvc\Router\Group();

        $modelApiClass = '';

        $api->setPrefix('/api/v1');
        $api->add('/:params', 'Errors::show404');

        $api->addGet('/model/{model}', ['controller' => $modelApiClass, 'action' => 'handle', 'method' => 'index'] );
        $api->addGet('/model/{model}/{id}', ['controller' => $modelApiClass, 'action' => 'handle', 'method' => 'show'] );
        $api->addPost('/model/{model}/search', ['controller' => $modelApiClass, 'action' => 'handle', 'method' => 'index'] );
        $api->addPost('/model/{model}', ['controller' => $modelApiClass, 'action' => 'handle', 'method' => 'store'] );
        $api->addPut('/model/{model}/{id}', ['controller' => $modelApiClass, 'action' => 'handle', 'method' => 'update'] );
        $api->addPost('/model/{model}/{id}', ['controller' => $modelApiClass, 'action' => 'handle', 'method' => 'update'] );
        $api->addDelete('/model/{model}/{id}', ['controller' => $modelApiClass, 'action' => 'handle', 'method' => 'destroy'] );

        $this->mount($api);
    }
}
