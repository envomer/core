<?php 

namespace Envo\Support;

class REST
{
    private $ch;
    
    private $headers = [];
    
    private $user;
    
    private $pass;
    
    public $response;
    
    public $httpCode;
    
    public $userAgent;
    
    private $options = [];
	
	/**
	 * Setup the request
	 *
	 * @param null $config
	 */
    public function __construct($config = null)
    {
        if($config) {
        	if(isset($config['headers'])) {
        		$this->setHeaders($config['headers']);
			}
        	
        	if(isset($config['auth_user'], $config['auth_pass'])) {
        		$this->setAuth($config['auth_user'], $config['auth_pass']);
			}
        }
		
		$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
    }
	
	/**
	 * @return $this
	 */
	public function disableSSLVerification() : self
	{
		$this->options[CURLOPT_SSL_VERIFYPEER] = false;
		$this->options[CURLOPT_SSL_VERIFYHOST] = false;
		
		return $this;
	}
	
	/**
	 * @return mixed
	 */
    public function getResponse()
    {
    	return $this->response;
    }
	
	/**
	 * @return mixed
	 */
    public function getHttpCode()
    {
    	return $this->httpCode;
    }
	
	/**
	 * @param $userAgent
	 *
	 * @return $this
	 */
	public function setUserAgent($userAgent) : self
	{
		$this->userAgent = $userAgent;
		
		return $this;
	}
	
	/**
	 * Send a GET request
	 *
	 * @param $url
	 * @param null $data
	 *
	 * @return REST
	 */
    public function get($url, $data = null) : self
    {    
        // Build and set the request URL
        $url = empty($data) ? $url : $url .'?'. http_build_query((array) $data);
        
        return $this->run(array(
        	CURLOPT_URL => $url,
        	CURLOPT_HTTPGET => true
        ));
    }
	
	/**
	 * Send a PUT request
	 *
	 * @param $url
	 * @param null $data
	 *
	 * @return REST
	 */
    public function put($url, $data = null) : self
	{
        return $this->run(array(
        	CURLOPT_URL => $url,
        	CURLOPT_CUSTOMREQUEST => 'PUT',
        	CURLOPT_POSTFIELDS => http_build_query((array) $data)
        ));
    }
	
	/**
	 * Send a POST request
	 *
	 * @param $url
	 * @param null $data
	 *
	 * @return REST
	 */
    public function post($url, $data = null) : self
	{
        return $this->run(array(
        	CURLOPT_URL => $url,
        	CURLOPT_POST => true,
        	CURLOPT_POSTFIELDS => $data
        ));
    }
	
	/**
	 * Send a DELETE request
	 *
	 * @param $url
	 *
	 * @return REST
	 */
    public function delete($url) : self
	{
        return $this->run(array(
        	CURLOPT_URL => $url,
        	CURLOPT_CUSTOMREQUEST => 'DELETE'
        ));
    }
	
	/**
	 * @param $option
	 * @param $value
	 *
	 * @return self
	 */
	public function setOption($option, $value) : self
	{
		$this->options[$option] = $value;
		
		return $this;
	}

	/**
	 * @param $option
	 * @param $value
	 *
	 * @return self
	 */
	public function setTimeout($value = 30, $connectTimeout = null) : self
	{
		$this->setOption(CURLOPT_TIMEOUT, $value);
		if($connectTimeout) {
			$this->setOption(CURLOPT_CONNECTTIMEOUT, $connectTimeout);
		}
		
		return $this;
	}
	
	/**
	 * Set basic auth
	 *
	 * @param $user
	 * @param $pass
	 *
	 * @return $this
	 */
    public function setAuth($user, $pass) : self
    {	
		$this->user = $user;
		$this->pass = $pass;
		
    	return $this;
    }
	
	/**
	 * @param $field
	 * @param $value
	 *
	 * @return void
	 */
    public function addHeader($field, $value)
    {
    	$this->headers[] = $field . ':' . $value;
    }
	
	/**
	 * Set headers
	 *
	 * @param $headers
	 *
	 * @return $this
	 */
    public function setHeaders($headers) : self
    {	
    	$this->headers = $headers;
    	
    	return $this;
    }
	
	/**
	 * Run the request and send response
	 *
	 * @param $curlOptions
	 *
	 * @return $this
	 * @throws \Exception
	 */
    private function run($curlOptions) : self
    {	
		// Init cURL
		$this->ch = curl_init();
		
		// Set default options
		// curl_setopt($this->ch, CURLOPT_TIMEOUT, 10000);
		
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);
		
		// Set headers
		if( $this->headers !== null && $this->headers ) {
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
		}
		
		// Set basic auth
		if( $this->user !== null && $this->pass !== null ) {
			curl_setopt($this->ch, CURLOPT_USERPWD, $this->user . ':' . $this->pass);
		}
		
		if($this->options) {
			foreach( $this->options as $option => $value ) {
				curl_setopt($this->ch, $option, $value);
			}
		}
		
		// Set custom options
		foreach( $curlOptions as $option => $value ) {
			curl_setopt($this->ch, $option, $value);
		}
		
		// Send the request
		$this->response = (string) curl_exec($this->ch);
		$this->httpCode = (int) curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		
		if($errorCode = curl_errno($this->ch)){
			$error = $errorCode . ': ' . curl_error($this->ch);
			curl_close($this->ch);
			throw new \Exception('Request Error: ' . $error);
		}
		
		curl_close($this->ch);
	
		
		return $this;
    }
    
}