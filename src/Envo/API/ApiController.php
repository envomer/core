<?php
namespace Envo\API;

use Envo\Auth;
use Core\Service\ModelRepo;
use Envo\AbstractController;

class ApiController extends AbstractController
{
    protected $api = null;
    public function handleAction($method, $model = null, $id = null)
    {
        /** TODO cache */
        $api = new Handler();
        $api->model = $model;

        require_once APP_PATH . 'app/api.php';
        $this->api = $api->getHandler();

        try {
            return $this->$method($model, $id);
        }
        catch(\Exception $e) {
            if( env('APP_ENV') == 'local' ) {
                envo_exception_handler($e);
            }

            return $this->json($e->getMessage());
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
}