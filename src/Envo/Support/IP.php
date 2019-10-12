<?php

namespace Envo\Support;

use Envo\Foundation\Config;

class IP
{
    /**
     * Whether to use proxy addresses or not.
     *
     * As default this setting is disabled - IP address is mostly needed to increase
     * security. HTTP_* are not reliable since can easily be spoofed. It can be enabled
     * just for more flexibility, but if user uses proxy to connect to trusted services
     * it's his/her own risk, only reliable field for IP address is $_SERVER['REMOTE_ADDR'].
     *
     * @var bool
     */
    protected $useProxy = false;

    /**
     * List of trusted proxy IP addresses
     *
     * @var array
     */
    protected $trustedProxies = array();

    /**
     * HTTP header to introspect for proxies
     *
     * @var string
     */
    protected $proxyHeader = 'HTTP_X_FORWARDED_FOR';

    protected static $ip = null;

    public function __construct()
    {
    	if (function_exists('config') && class_exists(Config::class)) {
			$this->useProxy = \config('app.proxy.enabled', false);
			$this->trustedProxies = \config('app.proxy.trusted_proxies', []);
		}
    }
	
	/**
	 * @return bool
	 */
	public function isUseProxy()
	{
		return $this->useProxy;
	}
	
	/**
	 * @param bool $useProxy
	 */
	public function setUseProxy($useProxy)
	{
		$this->useProxy = $useProxy;
	}
	
	/**
	 * @return array
	 */
	public function getTrustedProxies()
	{
		return $this->trustedProxies;
	}
	
	/**
	 * @param array $trustedProxies
	 */
	public function setTrustedProxies($trustedProxies)
	{
		$this->trustedProxies = $trustedProxies;
	}
	
	/**
	 * @return string
	 */
	public function getProxyHeader()
	{
		return $this->proxyHeader;
	}
	
	/**
	 * @param string $proxyHeader
	 */
	public function setProxyHeader($proxyHeader)
	{
		$this->proxyHeader = $proxyHeader;
	}
	
	/**
	 * @return null
	 */
	public static function getIp()
	{
		return self::$ip;
	}
	
	/**
	 * @param null $ip
	 */
	public static function setIp($ip)
	{
		self::$ip = $ip;
	}

    /**
     * Returns client IP address.
     *
     * @return string IP address.
     */
    public static function getIpAddress($instance = null)
    {
        if ( ! is_null(self::$ip) ) {
            return self::$ip;
        }
		
        $class = $instance ?: new self();
        $ip = $class->getIpAddressFromProxy();
        if ($ip) {
            return self::$ip = $ip;
        }

        // direct IP address
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return self::$ip = $_SERVER['REMOTE_ADDR'];
        }

        return self::$ip = '';
    }

    public function determine()
    {
        if ( $this->ip ) {
            return $this->ip;
        }

        $this->ip = $class->getIpAddressFromProxy();
        if ($this->ip) {
            return $this->ip;
        }

        // direct IP address
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $this->ip = $_SERVER['REMOTE_ADDR'];
        }

        return $this->ip = '';
    }

    public function isBlocked()
    {
        $path = APP_PATH . 'storage/blocked.txt';

        if ( ! file_exists($path) ) {
            return false;
        }

        $blockedIpsRaw = file_get_contents($path);

        if ( ! $blockedIpsRaw ) {
            return false;
        }

        $blockedIps = explode("\n", $blockedIpsRaw);

        if ( ! $blockedIps ) {
            return false;
        }

        if ( $this->ip && $isBlocked = in_array($this->ip, $blockedIps) !== false ) {
            echo include ENVO_PATH . 'View/html/ip-blocked.php';
            exit;
        }
    }

    /**
     * Attempt to get the IP address for a proxied client
     *
     * see http://tools.ietf.org/html/draft-ietf-appsawg-http-forwarded-10#section-5.2
     * @return false|string
     */
    protected function getIpAddressFromProxy()
    {
        if (!$this->useProxy
            || (isset($_SERVER['REMOTE_ADDR']) && !in_array($_SERVER['REMOTE_ADDR'], $this->trustedProxies))
        ) {
            return false;
        }

        $header = $this->proxyHeader;
        if (!isset($_SERVER[$header]) || empty($_SERVER[$header])) {
            return false;
        }

        return $_SERVER[$header];

        // Extract IPs
        $ips = explode(',', $_SERVER[$header]);
        // trim, so we can compare against trusted proxies properly
        $ips = array_map('trim', $ips);
        // remove trusted proxy IPs
        $ips = array_diff($ips, $this->trustedProxies);

        // Any left?
        if (empty($ips)) {
            return false;
        }

        // Since we've removed any known, trusted proxy servers, the right-most
        // address represents the first IP we do not know about -- i.e., we do
        // not know if it is a proxy server, or a client. As such, we treat it
        // as the originating IP.
        // @see http://en.wikipedia.org/wiki/X-Forwarded-For
        $ip = array_pop($ips);

        return $ip;
    }

    /**
     * Trace an ip address
     */
    public static function trace($ip = null)
    {
        if ( ! $ip ) {
            $ip = self::getIpAddress();
        }

        $resp = json_decode(file_get_contents('http://ip-api.com/json/' . $ip));

        if ( ! $resp || $resp->status != 'success' ) {
            return $resp->message;
        }

        return $resp;
    }
}