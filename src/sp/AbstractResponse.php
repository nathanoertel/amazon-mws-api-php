<?php
namespace AmazonMWS\sp;

abstract class AbstractResponse {
	protected $meta = null;

	protected $data = null;

	private $success = false;

	private $errors = null;

	private $error = null;

	private $errorCode = null;
	
	private $response = null;
	
	private $headers = array();

	public function isSuccess() {
		return $this->success;
	}

	public function getErrors() {
		return $this->errors;
	}

	public function getError() {
		return $this->error;
	}

	public function getErrorCode() {
		return $this->errorCode;
	}
	
	public function getRawResponse() {
		return $this->response;
	}
	
	public function getHeader($key) {
		if(isset($this->headers[$key])) return $this->headers($key);
		else return null;
	}

	public function getData() {
		return $this->data;
	}

	public function __construct($headers, $response, $method) {
		$this->response = $response;
		$response = json_decode($response, true);
		
		$headerArray = explode("\r\n", $headers);
		$httpCode = 500;
		$status = '';
	
		foreach($headerArray as $index => $header) {
			if(strpos($header, 'HTTP/') === 0) {
				list($httpType, $httpCode, $status) = explode(' ', $header);
				$httpCode = intval($httpCode);
			} else if(!empty($header)) {
				list($key, $value) = explode(': ', $header);
				$this->headers[$key] = $value;
			}
		}
		
		if($httpCode >= 200 && $httpCode < 300) {
			$this->success = true;
		} else {
			$this->errorCode = $httpCode;
			$this->error = $status;
		}

		if(isset($response['payload'])) {
			$this->data = $response['data'];
		}

		if(isset($response['errors'])) {
			$this->errors = $response['errors'];
		}
	}
}
?>