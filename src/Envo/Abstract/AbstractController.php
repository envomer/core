<?php

namespace Envo;

use Exception;

use Envo\Foundation\ExceptionHandler;
use Envo\Model\User;

use Phalcon\Mvc\Controller;

/**
 * Class AbstractController
 *
 * @package Envo
 */
class AbstractController extends Controller
{
	/**
	 * @var User
	 */
	protected $user;
	
	/**
	 * @var Auth
	 */
	protected $auth;
	
	public function initialize()
	{
		$module = explode('\\', static::class);
		$this->setViewsDir(current($module));
	}

	/**
	 * Set views directory
	 *
	 * @param string $module
	 * 
	 * @return void
	 */
	public function setViewsDir($module = 'Core')
	{
		$viewDirs = $this->view->getViewsDir();
		$viewDirs[] = APP_PATH . 'app/'. $module . '/View/';
		$this->view->setViewsDir($viewDirs );
	}

	/**
	 * Get user
	 *
	 * @return User
	 */
	public function user()
	{
		if($this->user) {
			return $this->user;
		}

		return $this->user = user();
	}
	
	/**
	 * Get parameter
	 *
	 * @param null $name
	 * @param null $default
	 *
	 * @return array|null|string|mixed
	 */
	public function get($name = null, $default = null)
	{
		$params = array_merge($this->router->getParams(), $_REQUEST);
		$requestMethods = array('PUT' => '', 'POST' => '', 'DELETE' => '');
		if( array_key_exists($_SERVER['REQUEST_METHOD'], $requestMethods) !== false ) {
			$rawInputData = file_get_contents('php://input');
			$post_vars = json_decode($rawInputData,true);

			if( ! is_array($post_vars) ) {
				parse_str($rawInputData,$post_vars);
			}

			if( $post_vars ) {
				$params = array_merge($params, $post_vars);
			}
		}

		if( null === $name ) {
			return $params;
		}

		if(isset($params[$name])) {
			return $params[$name];
		}

		return $default;
	}
	
	/**
	 * Return the response as a json
	 *
	 * @param      $msg
	 * @param null $sentence
	 * @param bool $includeState
	 *
	 * @return string
	 */
	public function json($msg, $sentence = null, $includeState = true)
	{
		/** TODO: refactor **/
		$code = 200;
		$loggedIn = ($this->user() && $this->user()->loggedIn);

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
			ExceptionHandler::handleError($msg);
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
			unset($msg['success'], $msg['authenticated']);
		}

		if( is_array($msg) ) {
			$msg['render_time'] = \render_time();
		} else {
			$msg->render_time = render_time();
		}

		if( array_key_exists('cc2', $_GET) ) {
			die(var_dump($msg));
		}

	    //Set the content of the response
	    $this->view->disable();
	    $this->response->setStatusCode($code);
	    $this->response->setContentType('application/json');
		
	    //Return the response
	    return $this->response->setJsonContent($msg)->send();
	}

	/**
	 * Empty json response
	 *
	 * @return string
	 */
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
	
	/**
	 * Abort unless user is admin
	 *
	 * @return boolean
	 */
	public function mustBeAdmin()
	{
		if( $this->user->isAdmin() ) {
			return true;
		}

		return $this->abort();
	}

	/**
	 * Abort unless user is logged in
	 *
	 * @return bool
	 */
	public function mustBeLoggedIn()
	{
		if( $this->user() && $this->user->loggedIn ) {
			return true;
		}

		return $this->abort(404);
	}
	
	/**
	 * Abort
	 *
	 * @param integer $code
	 * @param string  $msgCode
	 *
	 * @return bool
	 */
	public function abort($code = 403, $msgCode = 'app.unauthorized')
	{
		public_exception($msgCode, $code);
		
		return false;
	}
}
