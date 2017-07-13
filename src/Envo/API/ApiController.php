<?php
namespace Envo\API;

use Envo\Auth;
use Core\Service\ModelRepo;
use Envo\AbstractController;

use Envo\Exception\PublicException;
use Envo\Exception\InternalException;

class ApiController extends AbstractController
{
    protected $api = null;
    public function handleAction($method, $model = null, $id = null)
    {
        /** TODO cache */
        $this->api = $api = new Handler();
        $this->api->user = $this->getUser();

        $parameters = $this->get();
        $this->api->request = new RequestDTO($parameters);
        $this->api->request->parameters = $parameters;
        $api->name = $model;

        require_once APP_PATH . 'app/api.php';


        try {
            $api->setApi();
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
        $msgs = $this->api->getAll($page, $this->get());

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
        $resp = $this->api->save($this->get());
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
        $entries = $this->api->get($id, $this->get());
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
        $resp = $this->api->update($id, $this->get());
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
            $resp = $this->api->restore($id);
        }
        else {
            $resp = $this->api->delete($id, $this->get('force'), $this->get());
        }

        return $this->json( $resp );
    }

    public function notFoundAction()
    {
        return $this->json(false, 'Not found');
    }
}