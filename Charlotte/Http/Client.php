<?php

namespace Charlotte\Http;

class Client {

    public $useProxy;

    public $proxy;

    public $handle;

    public $url;

    public $timeout;    

    public $connectionTimeout;

    public $headers;  

    public $strictMode;    
    
    public $body;

    public $options;

    public $maxRedirects;

    public $maxRetries;

    public $response;

    public $responseCode;

    public $responseBody;

    public $responseHeader;

    public $responseError;

    public $responseInfo;

    public $useCache;
    
	public static $proxyAuths = array(
		'basic'		=>	CURLAUTH_BASIC,
		'ntlm'		=>	CURLAUTH_NTLM
	);

	public static $proxyType = array(
		'http'		=>	CURLPROXY_HTTP,
		'socks4'	=>	CURLPROXY_SOCKS4,
		'socks4a'	=>	6,	// CURLPROXY_SOCKS4A
		'socks5'	=>	CURLPROXY_SOCKS5,
	);

    public function __construct($opt = array())
    {
        $this->init($opt);
    }

    public function init(array $opt) {

        // by default: don't use proxy
        $this->useProxy = false;

        // the default proxy
        $this->proxy = array(
			'auth'	=>	'basic',
			'type'	=>	'http',
        );

        // the default curl handler
        $this->handle = curl_init();

        // the default request url
        $this->url = '';

        // the default timeout: 60secs
        $this->timeout = 60000;

        $this->connectionTimeout = 30000;

        // default headers is empty
        $this->headers = array();

        $this->strictMode = (array_key_exists('strict_mode', $opt) && $opt['strict_mode'] === false) ? false : true;

        $this->body = '';

        $this->options = array();

        $this->maxRedirects = 10;

        $this->maxRetries = 3;

        $this->response = false;

        $this->responseBody = false;

        $this->responseCode = false;

        $this->responseHeader = false;

        $this->responseError = false;

        $this->responseInfo = array();

        $this->useCache = false;
    }

    /**
     * Close the current connection
     */
    public function close() {
		if(false !== $this->handle)
		{
			curl_close($this->handle);
			$this->handle = false;
		}
    }

    public function __destruct() {
        $this->close();
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = false) {
        if( $this->has($key)) {
            return $this->{$key};
        }
        else {
            return $default;
        }
    }

    public function has($key) {
        return property_exists($this, $key) && isset($this->{$key});
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Client $this
     */
    public function set($key, $value) {
        if ($this->has($key)) {
            $this->{$key} = $value;
        } elseif ($this->isStrictMode()) {
            throw new \Exception('Property not found: ' . $key, 500);
        }

        return $this;
    }


    public function isStrictMode() {
        return $this->strictMode;
    }

    public function setUrl(string $url) {
        return $this->set('url', $url);
    }

    public function setBody($body) {
        return $this->set('body', $body);
    }

    public function setOptions(array $options) {
        foreach($options as $key => $value)
		{
			$this->options[$key] = $value;
        }
        
        return $this;
    }

    public function setOption($option, $value) {

        $this->options[$option] = $value;
        return $this;
    }

    public function setHeaders(array $headers)
	{
		$this->headers = array_merge($this->headers, $headers);
		return $this;
    }
    
    /**
	 * @param string $header
	 * @param string $value
	 * @return Client
	 */
	public function setHeader($header, $value)
    {
		$this->headers[$header] = $value;
		return $this;
	}

	/**
	 *
	 * @param int $timeout
	 * @param int $connectionTimeout
	 * @return Client 
	 */
	public function setTimeout(int $timeout = -1, int $connectionTimeout = -1)
	{
		if($timeout >= 0)
		{
			$this->timeout = $timeout;
        }
        
		if($connectionTimeout >= 0)
		{
			$this->connectionTimeout = $connectionTimeout;
        }
        
		return $this;
	}


	/**
	 * 
	 * @param string $server
	 * @param int $port
	 * @param string $type
	 * @param string $auth basic | ntlm
	 * @return Client $this 
	 */
	public function setProxy($server, $port, $type = 'http', $auth = 'basic')
	{
        $this->useProxy = true;

        $type = in_array($type, ['http', 'socks4', 'socks4a', 'socks5']) ? $type : 'http';

        $auth = in_array($auth, ['basic', 'ntlm']) ? $auth : 'basic';

		$this->proxy = array(
			'server'	=>	$server,
			'port'		=>	$port,
			'type'		=>	$type,
			'auth'		=>	$auth,
		);
		return $this;
    }

    public function useCache(bool $value) {
        $this->set('useCache', $value);
        return $this;
    }
    
    protected function parseHeaders()
	{
		curl_setopt($this->handle, CURLOPT_HTTPHEADER, $this->parseHeadersFormat());
    }
    
    protected function parseProxy()
	{
		if($this->useProxy)
		{
			curl_setopt_array($this->handle, array(
				CURLOPT_PROXYAUTH	=> self::$proxyAuths[$this->proxy['auth']],
				CURLOPT_PROXY		=> $this->proxy['server'],
				CURLOPT_PROXYPORT	=> $this->proxy['port'],
				CURLOPT_PROXYTYPE	=> 'socks5' === $this->proxy['type'] ? (defined('CURLPROXY_SOCKS5_HOSTNAME') ? CURLPROXY_SOCKS5_HOSTNAME : self::$proxyType[$this->proxy['type']]) : self::$proxyType[$this->proxy['type']],
			));
		}
    }
    
    protected function parseHeadersFormat()
	{
		$headers = array();
		foreach($this->headers as $name => $value)
		{
			$headers[] = $name . ':' . $value;
        }

		return $headers;
    }
    



	/**
	 * 
	 * @param string $url
	 * @param array $requestBody
	 * @param array $method
	 * @return Client 
	 */
	public function send($method = 'GET', $url = null, $body = array())
	{
		if(null !== $url) {
			$this->url = $url;
		}
        
        if(!empty($body)) {
			if(is_array($body)) {
				$this->body = http_build_query($body, '', '&');
			} elseif (false) {
                // TODO: multi part curl support
			}
			else {
				$this->body = $body;
			}
        }
        
		$options = array(

			CURLOPT_CUSTOMREQUEST	=> $method,

			CURLOPT_RETURNTRANSFER	=> true,

			CURLOPT_HEADER			=> true,

            CURLOPT_POSTFIELDS		=> $this->body,
            
            CURLOPT_FRESH_CONNECT   => !$this->useCache,

            // TODO: cookie support
			// CURLOPT_COOKIEFILE		=> '',
			// CURLOPT_COOKIEJAR		=> '',

			//CURLOPT_FOLLOWLOCATION	=> false,

			CURLOPT_MAXREDIRS		=> $this->maxRedirects,
		);
		
		if(isset($this->headers['Accept-Encoding'])) {
			$options[CURLOPT_ENCODING] = $this->headers['Accept-Encoding'];
		} else {
			$options[CURLOPT_ENCODING] = '';
		}
		curl_setopt_array($this->handle, $options);

		$this->parseProxy();
		$this->parseHeaders();


        curl_setopt($this->handle, CURLOPT_URL, $url);
        $i = 0;
        for($i = 0; $i <= $this->maxRetries; ++$i)
        {
            $response = curl_exec($this->handle);
            $this->set('response', $response);

            if(curl_errno($this->handle)) {
                continue;
            }

            $this->set('responseInfo', curl_getinfo($this->handle));
            $code = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
            $this->set('responseCode', $code);

            $header_size = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);

            $this->set('responseHeader', substr($response, 0, $header_size));
            $this->set('responseBody', substr($response, $header_size));
            
            if(!(0 === $code || (5 === (int)($code/100))))
            {
            	break;
            }
        }

        if ($i > $this->maxRetries) {
            if (curl_errno($this->handle)) {
                $this->responseError = curl_error($this->handle);
            } else {
                $this->responseError = 'unknown error';
            }
        }

		return $this;
    }
    
    public function getResponseStatusCode() {
        return $this->get('responseCode');
    }

    public function getResponse() {
        return $this->get('response');
    }

    public function getResponseHeader() {
        return $this->get('responseHeader');
    }

    public function getResponseBody() {
        return $this->get('responseBody');
    }

    public function getResponseInfo(string $key = '') {
        if ($key === '') {
            return $this->get('responseInfo');
        } else {
            return array_key_exists($key, $this->get('responseInfo')) ? $this->get('responseInfo') : null;
        }
        
    }
    
	/**
	 * GET
	 * @param string $url
	 * @param array $requestBody
	 * @return mixed 
	 */
	public function sendGet($url = null, $body = array())
	{
		if(!empty($body))
		{
			if(strpos($url, '?'))
			{
				$url .= '&';
			}
			else
			{
				$url .= '?';
			}
			$url .= http_build_query($body, '', '&');
		}
		return $this->send('GET', $url, array());
    }
    

	/**
	 * POST
	 * @param string $url
	 * @param array $body
	 * @return mixed 
	 */
	public function sendPost($url = null, $body = array())
	{
		return $this->send('POST', $url, $body);
	}

}