<?php
namespace Envo\API;

use Envo\Auth;
use Core\Service\ModelRepo;
use Envo\AbstractController;

use Envo\Exception\PublicException;
use Envo\Exception\InternalException;

class ApiController extends AbstractController
{
    protected $apiHandler = null;

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
        $this->apiHandler = $api = new Handler();
        $this->apiHandler->user = $this->getUser();

        $parameters = $this->get();
        $this->apiHandler->request = new RequestDTO($parameters);
        $this->apiHandler->request->parameters = $parameters;
        $this->apiHandler->name = $model;

        require_once APP_PATH . 'app/api.php';

        try {
            $this->apiHandler->setApi();
            if( ! $this->apiHandler->isAuthorized() ) {
                \public_exception('app.unauthorized', 403);
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
        return $this->json(false, 'Not found');
    }
}