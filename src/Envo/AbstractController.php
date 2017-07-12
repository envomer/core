<?php

namespace Envo;

class AbstractController extends \Phalcon\Mvc\Controller
{
	protected $user = null;

	public function setViewsDir($module = 'Core')
	{
		$this->view->setViewsDir( APP_PATH . 'app/'. $module . '/views/' );
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
		$code = 200;
		if( is_bool($msg) ) $msg = ['success' => $msg];
		else if( is_string($msg) ) {
			if( \_t('app.notfound') == $msg ) $code = 404;
			else if( \_t('app.notallowed') == $msg ) $code = 403;

			$msg = [
				'success' => false,
				'msg' => $msg
			];
		}
		else if( is_array($msg) && isset($msg[0]) && is_a($msg[0], 'Phalcon\Mvc\Model\Message') ) {
			$errors = ['success' => false];
			foreach ($msg as $mg) {
				$errors['validation'][] = $mg->getMessage();
			}
			$msg = $errors;
			$code = 400;
		}
		else if( is_array($msg) && ! isset($msg['success']) ) {
			$msg['success'] = true;
		}
		else if( is_a($msg, AbstractException::class)) {
			$msg = [
				'msg' => is_a($msg, PublicException) ? $msg->message : 'Something went terribly wrong.',
				'success' => $msg->success,
				'data' => $msg->data,
				'reference' => $msg->reference
			];
		}

		if( $sentence ) {
			$msg['msg'] = $sentence;
		}

		$loggedIn = $this->getUser() && $this->getUser()->loggedIn ? true : false;;

		if(is_array($msg)) $msg['loggedIn'] = $loggedIn;
		else if( is_object($msg) ) $msg->loggedIn = $loggedIn;

	    //Set the content of the response
	    $this->view->disable();
	    $this->response->setStatusCode($code);
	    $this->response->setContentType('application/json');

	    if( ! $includeState ) {
	    	unset($msg['success']);
	    	unset($msg['loggedIn']);
	    }

		$msg->render_time = render_time();

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
