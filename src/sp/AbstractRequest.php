<?php
namespace AmazonMWS\sp;

abstract class AbstractRequest {
	const BASE_URL = 'https://sandbox.sellingpartnerapi-na.amazon.com';
	// const BASE_URL = 'https://sellingpartnerapi-na.amazon.com';

	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
	const DELETE = 'DELETE';

	public $env;

	protected $config;

	private $logger;

	/**
	 * @param array $config
	 * @param string $env
	 * @throws \Exception
	 */
	public function __construct(Configuration $config, $logger = null) {
		$this->config = $config;
		
		$this->logger = $logger;
	}

	public function get($path, $parameters = null) {
		return $this->request(self::GET, $path, $parameters);
	}

	public function post($path, $parameters, $data) {
		return $this->request(self::POST, $path, $parameters, $data);
	}

	public function put($path, $parameters, $data) {
		return $this->request(self::PUT, $path, $parameters, $data);
	}

	public function delete($path, $parameters) {
		return $this->request(self::DELETE, $path, $parameters);
	}

	private function request($method, $path, $parameters = null, $data = null) {
		$result = false;

		$this->getAccessToken();

		return false;

		$url = self::BASE_URL.$this->getPath().$path;

    if (!empty($parameters)) {
			ksort($parameters);
			$query = http_build_query(
				$parameters
			);
		} else $query = false;

		$curl = curl_init();

		$options = array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url . ($query ? '?' . $query : ''),
			CURLOPT_HEADER => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLINFO_HEADER_OUT => true
		);

		$httpHeaders = array();

		if($method == self::GET) {
			$this->log('GET '.$options[CURLOPT_URL]);
		} else if($method == self::PUT) {
			$options[CURLOPT_POST] = 1;
			$options[CURLOPT_POSTFIELDS] = $this->getPostFields($data);
			$options[CURLOPT_CUSTOMREQUEST] = 'PUT';
			$this->log('PUT '.$options[CURLOPT_URL]);
			$this->log(json_encode($data));
		} else if($method == self::POST) {
			$options[CURLOPT_POST] = 1;
			$options[CURLOPT_POSTFIELDS] = $this->getPostFields($data);
			$this->log('POST '.$options[CURLOPT_URL]);
			$this->log(json_encode($data));
		} else if($method == self::DELETE) {
			$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
			$this->log('DELETE '.$options[CURLOPT_URL]);
		}

		$headers = $this->getHeaders($options[CURLOPT_URL], $method, $httpHeaders);
		$amazonHeader = Signature::calculateSignature(
			$this->config,
			str_replace('https://', '', $this->config->getHost()),
			$method,
			$this->getPath().$path,
			$query
			// (string) $httpBody,
		);

		foreach($amazonHeader as $key => $value) {
			$headers[] = $key.': '.$value;
		}

		foreach($headers as $header) $this->log($header);

		$options[CURLOPT_HTTPHEADER] = $headers;

		curl_setopt_array($curl, $options);

		$response = curl_exec($curl);
		$information = curl_getinfo($curl);
		
		$this->log($information['request_header']);

		if($response !== false) {
      // error_log($response);
			$this->log($response);
			
			$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

			$headers = substr($response, 0, $headerSize);
			$body = substr($response, $headerSize);

      $responseClass = $this->getResponseClass();

			$result = new $responseClass($headers, $body, $method);

			unset($headerSize, $headers, $body);
		} else {
      error_log(curl_error($curl));
			$this->log(curl_error($curl));
		}
		
		curl_close($curl);

		return $result;
	}

	private function getAccessToken() {
		$url = self::BASE_URL.'/auth/o2/token';

		$curl = curl_init();

		$options = array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url,
			CURLOPT_HEADER => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLINFO_HEADER_OUT => true
		);

		$httpHeaders = array();

		$data = array(
			'grant_type' => 'client_credentials',
			'scope' => 'sellingpartnerapi::migration',
			'client_id' => 'amzn1.application-oa2-client.d3ef09c530e54f7488387940e71e0bde',//$this->config->getClientId(),
			'client_secret' => 'deae5c69aa0f4831c4286f858c742e8775af067fbcd248ac83c2fd61dc7aab30'//$this->config->getClientSecret(),
		);

		$options[CURLOPT_POST] = 1;
		$options[CURLOPT_POSTFIELDS] = http_build_query($data);
		$this->log('POST '.$options[CURLOPT_URL]);
		$this->log($options[CURLOPT_POSTFIELDS]);

		$headers = $this->getHeaders($options[CURLOPT_URL], $method, $httpHeaders);
		$amazonHeader = Signature::calculateSignature(
			$this->config,
			str_replace('https://', '', $this->config->getHost()),
			$method,
			'/auth/o2/token',
			'',
			$options[CURLOPT_POSTFIELDS]
		);

		foreach($amazonHeader as $key => $value) {
			$headers[] = $key.': '.$value;
		}

		foreach($headers as $header) $this->log($header);

		foreach($headers as $header) $this->log($header);

		$options[CURLOPT_HTTPHEADER] = $headers;

		curl_setopt_array($curl, $options);

		$response = curl_exec($curl);
		$information = curl_getinfo($curl);
		
		$this->log($information['request_header']);

		if($response !== false) {
      // error_log($response);
			$this->log($response);
			
			$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

			$headers = substr($response, 0, $headerSize);
			$body = substr($response, $headerSize);

      $responseClass = $this->getResponseClass();

			$result = new $responseClass($headers, $body, $method);

			unset($headerSize, $headers, $body);
		} else {
      error_log(curl_error($curl));
			$this->log(curl_error($curl));
		}
		
		curl_close($curl);

		return $result;
	}
	
	protected function getPostFields($data) {
		return json_encode($data);
	}
	
	protected function getPostContentType() {
		return 'Content-Type: application/json';
	}

	protected abstract function getPath();

	public function getHeaders($url, $method, $headers = array()) {
		$headers[] = 'Accept: application/json';

    if ($method === self::POST || $method === self::PUT) {
      $headers[] = $this->getPostContentType();
    }

		return $headers;
	}

  protected function getResponseClass() {
    return '\AmazonMWS\sp\GenericResponse';
  }

	private function log($message) {
		if($this->logger) $this->logger->info($message);
	}
}