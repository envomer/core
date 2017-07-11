<?php
namespace Envo\API;

use Envo\Auth;
use Core\Service\ModelRepo;
use Envo\AbstractController;

class ApiController extends AbstractController
{
    /**
     * Construct a new model repo and enable all the basic
     * rest api functions
     */
    public function initialize()
    {
        parent::initialize();
        $this->mustBeLoggedIn();

        $repo = new ModelRepo($this->user, $this->getModules());
        $params = $this->router->getParams();
        $model = ( isset($params['model']) ) ? $params['model'] : null;
        $this->repo = $repo->get($model);
    }

    public function handleAction($method, $model = null, $id = null)
    {
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
        $msgs = $this->repo->getAll($page, $this->get());

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
        $resp = $this->repo->save($this->get());
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
        $entries = $this->repo->get($id, $this->get());
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
        $resp = $this->repo->update($id, $this->get());
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
            $resp = $this->repo->restore($id);
        }
        else {
            $resp = $this->repo->delete($id, $this->get('force'), $this->get());
        }

        return $this->json( $resp );
    }
}