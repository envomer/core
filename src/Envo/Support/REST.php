<?php 

namespace Envo\Support;

class REST
{    
    protected $ch, $headers = [], $user, $pass;
    
    public $response, $httpCode;
    
    // Setup the object
    public function __construct($config = null)
    {
        if($config) {
	        $this->setHeaders($config['headers']);
	        $this->setAuth($config['auth_user'], $config['auth_pass']);
        }
        
    }

    public function getResponse()
    {
    	return $this->response;
    }

    public function getHttpCode()
    {
    	return $httpCode;
    }
	
    // Send a GET request
    public function get($url, $data = null)
    {    
        // Build and set the request URL
        $url = (empty($data)) ? $url : $url .'?'. http_build_query((array) $data);
        
        return $this->run(array(
        	CURLOPT_URL => $url,
        	CURLOPT_HTTPGET => true
        ));
        
    }
    
    // Send a PUT request
    public function put($url, $data = null)
    {    
        return $this->run(array(
        	CURLOPT_URL => $url,
        	CURLOPT_CUSTOMREQUEST => 'PUT',
        	CURLOPT_POSTFIELDS => http_build_query((array) $data)
        ));
        
    }
    
    // Send a POST request
    public function post($url, $data = null)
    {    
        return $this->run(array(
        	CURLOPT_URL => $url,
        	CURLOPT_POST => true,
        	CURLOPT_POSTFIELDS => $data
        ));
        
    }
    
    // Send a delete request
    public function delete($url)
    {    
        return $this->run(array(
        	CURLOPT_URL => $url,
        	CURLOPT_CUSTOMREQUEST => 'DELETE'
        ));
        
    }
    
    // Set basic auth
    public function setAuth($user, $pass)
    {	
		$this->user = $user;
		$this->pass = $pass;
		
    	return $this;
    	
    }

    public function addHeader($field, $value)
    {
    	$this->headers[] = $field . ':' . $value;
    }
    
    // Set headers
    public function setHeaders($headers)
    {	
    	$this->headers = $headers;
    	
    	return $this;
    	
    }
    
    // Run the request and send response
    private function run($curl_opts)
    {	
		// Init cURL
		$this->ch = curl_init();
		
		// Set default options
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		
		// Set headers
		if( isset($this->headers) ) {
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
		}
		
		// Set basic auth
		if( isset($this->user) && isset($this->pass) ) {
			curl_setopt($this->ch, CURLOPT_USERPWD, $this->user . ':' . $this->pass);
		}
		
		// Set custom options
		foreach( $curl_opts as $option => $value ) {
			curl_setopt($this->ch, $option, $value);
		}
		
		// Send the request
		$this->response = (string) curl_exec($this->ch);
		$this->httpCode = (int) curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		curl_close($this->ch);
		
		return $this;
    }
    
}