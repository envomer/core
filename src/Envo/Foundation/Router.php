<?php

namespace Envo\Foundation;

use Envo\API\Handler;
use Envo\Extension\EmailTemplate\API;
use Illuminate\Support\Facades\Route;

class Router
{
	/**
	 * @var self
	 */
    private static $router;
	
	/**
	 * @var Handler
	 */
    private $apiHandler;

    private $group;
	
	/**
	 * @var string
	 */
    public $apiPrefix = 'api/1';
	
	/**
	 * @return Router
	 */
    public static function getInstance()
    {
        if( self::$router ) {
            return self::$router;
        }
		
        return self::$router = new self();
    }

    public function add($name, $path)
    {
        return Route::any($name, $this->parseRoute($path));
    }
    
    public function addGet($name, $path)
    {
        return Route::get($name, $this->parseRoute($path));
    }
    
    public function addPost($name, $path)
    {
        return Route::post($name, $this->parseRoute($path));
    }
    
    public function parseRoute($path)
    {
        $parts = explode('::', $path);
        $parts[0] .= 'Controller';
    
        if (!class_exists($parts[0])) {
            $parts[0] = 'Core\\Controller\\' . $parts[0];
        }
    
        $parts[1] .= 'Action';
        
        return $parts;
    }
	
	/**
	 * @param $name
	 * @param $path
	 */
    public function get($name, $path)
    {
        $this->add($name, $path);
    }
	
	/**
	 * @return Group
	 */
    public function api()
    {
        $api = new Group();
        
		$api->setPrefix('/' . $this->apiPrefix);
		
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

        $this->group = $api;

        return $api;
    }
	
	/**
	 * @param string $name
	 * @param string $class
	 */
    public function addApi($name, $class)
    {
    	if(!$this->apiHandler) {
    		return;
		}
		
        if(strpos($name, '/')) {
            // die(var_dump($name));
            $this->addApiGroup($name);
        }

        $this->apiHandler->add($name, $class);
    }
	
	/**
	 * @param Handler $handler
	 */
    public function setHandler($handler)
    {
        $this->apiHandler = $handler;
    }
	
	/**
	 * @TODO refactor to extension/placeholder
	 */
	public function extensions()
	{
		if(!$this->apiHandler) {
			return;
		}
		
		$this->addGet('/placeholder/{size}', [
			'namespace' => 'Envo\Extension\Placeholder',
			'controller' => 'PlaceholderApi',
			'action' => 'render'
		]);
		
		$this->apiHandler->add('ex-email-template', API::class);
	}

    protected function addApiGroup($path)
    {
        // die(var_dump($this->group));
        $this->group->addGet('/'.$path, [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'index'
        ])->setName($path . '.index');
        
        $this->group->addGet('/'.$path.'/{id}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'show',
            // 'id' => 3,
            // 'model' => 4
        ])->setName($path . '.show');
        
        $this->group->addPost('/'.$path.'/search', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'index'
        ])->setName($path . '.search');
        
        $this->group->addPost('/'.$path, [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'store'
        ])->setName($path . '.store');
        
        $this->group->add('/'.$path.'/{id}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'update'
        ], ['POST', 'PUT'])->setName($path . '.update');
        
        $this->group->addDelete('/'.$path.'/{id}', [
            'namespace' => 'Envo\API',
            'controller' => 'Api',
            'action' => 'handle',
            'method' => 'destroy'
        ])->setName($path . '.destroy');
    }
	
	/**
	 * @param $cached
	 *
	 * @return void
	 */
	public function import($cached)
	{
		$this->_routes = $cached['routes'] ?? [];
		$this->_removeExtraSlashes = $cached['removeExtraSlashes'] ?? false;
		$this->apiPrefix = $cached['apiPrefix'] ?? '';
		$this->notFoundPaths = $cached['notFoundPaths'] ?? [];
	}
	
	/**
	 * @return array
	 */
	public function export()
	{
		return [
			'routes' => $this->_routes,
			'apiPrefix' => $this->apiPrefix,
			'notFoundPaths' => $this->_notFoundPaths,
			'removeExtraSlashes' => $this->_removeExtraSlashes,
			'apis' => $this->apiHandler->apis
		];
	}
}
