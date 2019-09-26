<?php
namespace AmazonMWS;

use AmazonMWS\exception\InvalidTypeException;

abstract class AbstractRequest {
	/**
	 * <code>
	 * $config = array(
	 * 	'name' => 'App Name',
	 * 	'version' => 'App Version',
	 * 	'accessKeyId' => 'MWS Access Key ID', // * Required
	 * 	'secretAccessKey' => 'MWS Secret Access Key', // * Required
	 * 	'merchantId' => 'Merchant/Seller ID',
	 * 	'authToken' => 'MWS Auth Token' // * Required if calling for merchant not associated with the access and secret key
	 * );
	 * </code>
	 * @var $config array[string]string
	 */
	protected $config = array(
		'name' => 'inverge',
		'version' => '1.0.1'
	);

	protected $options = array(
		'ProxyHost' => null,
		'ProxyPort' => -1,
		'ProxyUsername' => null,
		'ProxyPassword' => null,
		'MaxErrorRetry' => 0
	);

	private $logger;

	/**
	 * @param array $config
	 * @param string $env
	 * @throws \Exception
	 */
	public function __construct(array $config, array $options = array(), $logger = null) {
		// check that the necessary keys are set
		if(!isset($config['accessKeyId']) || !isset($config['secretAccessKey'])) {
			throw new \Exception('Configuration missing access key id or secret access key');
		}
	
		$this->config = array_merge($this->config, $config);

		// Apply some defaults.
		$this->options = array_merge($this->options, $options);
		
		$this->logger = $logger;
	}

	public function request($method, $request) {
		if(isset($this->config['merchantId'])) {
			$request['Merchant'] = $this->config['merchantId'];
			$request['SellerId'] = $this->config['merchantId'];
		}
		if(isset($this->config['authToken']) && !empty($this->config['authToken'])) $request['MWSAuthToken'] = $this->config['authToken'];
		
		$clientName = $this->getPath().'_Client';

		$this->requireFile($clientName);

		$options = $this->options;
		$options['ServiceURL'] = $this->getURL();
		
		$service = new $clientName(
			$this->config['accessKeyId'],
			$this->config['secretAccessKey'],
			$this->config['name'],
			$this->config['version'],
			$options,
			$this->logger
		);

		try {
			$response = $service->$method($request);
			return $response;
		} catch(Exception $ex) {
			$this->log("Caught Exception: " . $ex->getMessage(), true);
			$this->log("Response Status Code: " . $ex->getStatusCode(), true);
			$this->log("Error Code: " . $ex->getErrorCode(), true);
			$this->log("Error Type: " . $ex->getErrorType(), true);
			$this->log("Request ID: " . $ex->getRequestId(), true);
			$this->log("XML: " . $ex->getXML(), true);
			$this->log("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata(), true);
			
			throw($ex);
		}
	}

	public abstract function getPath();

	public abstract function getURL();

	public function setLogger($type, $arguments = array()) {
		$path = $this->getPath();
		$clientName = $path.'_Logger_'.$type;
		$this->requireFile($clientName);
		$class = new \ReflectionClass($clientName);
		if(empty($arguments)) $this->logger = $class->newInstance();
		else $this->logger = $class->newInstanceArgs($arguments);
	}

	private function requireFile($classname) {
		$fullPath = dirname(__FILE__).'/lib/'.str_replace('_', '/', $classname).'.php';

		if(file_exists($fullPath)) {
			require_once($fullPath);
		} else {
			throw new InvalidTypeException($classname.' is not a valid type');
		}
	}

	protected function log($message, $error = false) {
		if($this->logger) $this->logger->log($message, $error);
	}
}