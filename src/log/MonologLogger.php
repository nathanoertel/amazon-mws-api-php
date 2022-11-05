<?php
namespace AmazonMWS\log;

class MonologLogger implements Logger {
	
	private $logger;
	
	public function log($message, $error = false) {
		if ($error) $this->logger->error($message);
		else $this->logger->info($message);
	}
	
	public function __construct($logger) {
		$this->logger = $logger;
	}
}