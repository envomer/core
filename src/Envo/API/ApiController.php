<?php
namespace Envo\API;

use Envo\Auth;
use Envo\AbstractController;

use Envo\Exception\PublicException;
use Envo\Exception\InternalException;

class ApiController extends AbstractController
{
    protected $apiHandler = null;

    /**
     * Authenticate user
     *
     * @return array|string
	 */
    public function authenticateAction()
    {
        $email = $this->get('email');
        $password = $this->get('password');

        try {
            $response = $this->di->get('auth')->check($email, $password);
        } catch (\Exception $exception) {
            return $this->json($exception);
        }

        $user = user();

        return $this->json([
            'data' => [
                'api_key' => $user->getApiKey(),
                'identifier' => $user->getIdentifier(),
                'username' => $user->username,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * Handle all api requests
     *
     * @param [type] $method
     * @param [type] $model
     * @param [type] $id
     * @return void
     */
    public function handleAction($method, $model = null, $id = null)
    {
        /** TODO cache */
        $this->apiHandler = $this->di->get('apiHandler');
        $this->apiHandler->user = $this->user();

        $router = $this->router;
        $route = $router->getMatchedRoute();
        $name = null;

        if(strpos($route->getName(), '.') !== false) {
            $name = str_replace('.' . $method, '', $route->getName());
            $id = $model;
        }
		
        $parameters = $this->get();
        $this->apiHandler->request = new RequestDTO($parameters);
        $this->apiHandler->request->parameters = $parameters;
        $this->apiHandler->name = $model;

        $router = $this->router;

        try {
            $this->apiHandler->setApi($name);
            if( ! $this->apiHandler->isAuthorized() ) {
                public_exception('app.unauthorized', 403);
            }

            return $this->$method($model, $id);
        }
        catch(\Exception $e) {
            return $this->json($e);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $page = $this->get('page', 1);
        $msgs = $this->apiHandler->getAll($page, $this->get());

        return $this->json($msgs);
    }
	
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param $model
	 *
	 * @return Response
	 */
    public function store($model)
    {
        $resp = $this->apiHandler->save($this->get());

        return $this->json($resp);
    }
	
	/**
	 * Display the specified resource.
	 *
	 * @param      $model
	 * @param  int $id
	 *
	 * @return Response
	 */
    public function show($model, $id)
    {
        $entries = $this->apiHandler->get($id, $this->get());

        return $this->json( $entries );
    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param      $model
	 * @param  int $id
	 *
	 * @return Response
	 */
    public function update($model, $id)
    {
        $resp = $this->apiHandler->update($id, $this->get());

        return $this->json( $resp );
    }
	
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param      $model
	 * @param  int $id
	 *
	 * @return Response
	 */
    public function destroy($model, $id)
    {
        if( $this->get('restore') ) {
            $resp = $this->apiHandler->restore($id);
        }
        else {
            $resp = $this->apiHandler->delete($id, $this->get('force'), $this->get());
        }

        return $this->json( $resp );
    }

    /**
     * Api endpoint not found
     *
     * @return void
     */
    public function notFoundAction()
    {
        public_exception('api.notFound', 404);
    }
}