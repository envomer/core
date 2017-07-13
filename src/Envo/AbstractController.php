<?php

namespace Envo;

use Envo\Exception\PublicException;
use Envo\AbstractException;
use Envo\Support\Str;
use Phalcon\Mvc\Controller;
use Exception;

class AbstractController extends Controller
{
	protected $user = null;

	public function setViewsDir($module = 'Core')
	{
		$this->view->setViewsDir( APP_PATH . 'app/'. $module . '/View/' );
	}

	public function getUser()
	{
		if($this->user) {
			return $this->user;
		}
		return $this->user = Auth::user();
	}

	public function user()
	{
		return $this->getUser();
	}
	
	/**
	 * Get parameter
	 *
	 * @param null $name
	 * @param null $default
	 *
	 * @return array|null
	 */
	public function get($name = null, $default = null)
	{
		$params = array_merge($this->router->getParams(), $_REQUEST);
		$requestMethods = array('PUT' => '', 'POST' => '', 'DELETE' => '');
		if( array_key_exists($_SERVER['REQUEST_METHOD'], $requestMethods) !== false ) {
			$rawInputData = file_get_contents("php://input");
			$post_vars = json_decode($rawInputData,true);

			if( ! is_array($post_vars) ) {
				parse_str($rawInputData,$post_vars);
			}

			if( $post_vars ) {
				$params = array_merge($params, $post_vars);
			}
		}
		if( is_null($name) ) {
			return $params;
		}
		if(isset($params[$name])) {
			return $params[$name];
		}
		return $default;
	}

  	/**
	 * Return the reponse as a json
	 */
	public function json($msg, $sentence = null, $includeState = true)
	{
		/** TODO: refactor **/
		$code = 200;
		$loggedIn = $this->getUser() && $this->getUser()->loggedIn ? true : false;;

		if( is_bool($msg) ) {
			$msg = ['success' => $msg];
		}
		else if( is_string($msg) ) {
			$msg = [
				'success' => false,
				'message' => $msg
			];
		}
		else if( is_array($msg) && ! isset($msg['success']) ) {
			$msg['success'] = true;
		}
		else if( $msg instanceof Exception ) {
			if( ! is_subclass_of($msg, AbstractException::class) )  {
				$msg = uncaught_exception($msg);
			}

			$code = $msg->getCode();
			$msg = $msg->json();
		}

		if( $sentence ) {
			$msg['message'] = $sentence;
		}

		if(! $loggedIn && is_array($msg)) {
			$msg['authenticated'] = $loggedIn;
		}
		else if( ! $loggedIn && is_object($msg) ) {
			$msg->authenticated = $loggedIn;
		}

	    if( ! $includeState ) {
	    	unset($msg['success']);
	    	unset($msg['authenticated']);
	    }

		$msg->render_time = render_time();

	    //Set the content of the response
	    $this->view->disable();
	    $this->response->setStatusCode($code);
	    $this->response->setContentType('application/json');
		
	    //Return the response
	    return $this->response->setJsonContent($msg)->send();
	}

	public function emptyApiJson()
	{
		return $this->json([
			'first' => 1,
			'before' => 1,
			'last' => 1,
			'next' => 1,
			'total_pages' => 1,
			'total_items' => 0,
			'data' => [],
			'current' => 1
		]);
	}

	public function mustBeAdmin()
	{
		if( $this->user->isAdmin() ) {
			return true;
		}

		$this->abort();
	}

	public function mustBeLoggedIn()
	{
		if( $this->user && $this->user->loggedIn ) {
			return true;
		}

		$this->abort(404);
	}

	public function abort($code = 403, $msg = 'Unauthorized')
	{
		throw new \Exception($msg, $code);
	}
}
