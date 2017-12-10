<?php

namespace Envo\Foundation;

class Router extends \Phalcon\Mvc\Router
{
    private static $router;
    private $apiHandler = null;
    
    //public $version = 'v.1';
    public $apiPrefix = '/api/1';

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
        
		$api->setPrefix($this->apiPrefix);
		
        $api->add('/:params', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'notFound'
        ]);

        $api->addGet('/{model}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'index'
        ])->setName('api-index');
        
        $api->addGet('/{model}/{id}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'show'
        ])->setName('api-show');
        
        $api->addPost('/{model}/search', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'index'
        ])->setName('api-search');
        
        $api->addPost('/{model}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'store'
        ])->setName('api-store');
        
        $api->add('/{model}/{id}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'update'
        ], ['POST', 'PUT'])->setName('api-update');
        
        //$api->addPost('/{model}/{id}', [
        //    'namespace' => 'Envo\API',
        //    'controller' => 'Api',
        //    'action' => 'handle',
        //    'method' => 'update'
        //])->setName('api-update');
        
        $api->addDelete('/{model}/{id}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'destroy'
        ])->setName('api-delete');

        $api->addPost('/authenticate', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'authenticate',
        ])->setName('api-authenticate');

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
