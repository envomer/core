<?php

namespace Envo\Foundation;

class Router extends \Phalcon\Mvc\Router
{
    private static $router;
    private $apiHandler = null;

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

        $api->setPrefix('/api/v1');
        $api->add('/:params', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'notFound'
        ]);

        $api->addGet('/model/{model}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'index'
        ]);
        $api->addGet('/model/{model}/{id}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'show'
        ]);
        $api->addPost('/model/{model}/search', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'index'
        ]);
        $api->addPost('/model/{model}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'store'
        ]);
        $api->addPut('/model/{model}/{id}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'update'
        ]);
        $api->addPost('/model/{model}/{id}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'update'
        ]);
        $api->addDelete('/model/{model}/{id}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'destroy'
        ]);

        $api->addPost('/authenticate', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'authenticate',
        ]);

        return $api;
    }

    public function addApi($name, $class)
    {
        $this->apiHandler->add($name, $class);
    }

    public function setHandler($handler)
    {
        $this->apiHandler = $handler;
    }
}
