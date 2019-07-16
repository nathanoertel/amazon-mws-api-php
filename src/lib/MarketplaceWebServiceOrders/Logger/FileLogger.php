<?php
class MarketplaceWebServiceOrders_Logger_FileLogger {
	
	private $infoFile = null;
	private $errorFile = null;
	
	public function log($message, $error = false) {
		error_log($message."\n", 3, $error ? $this->errorFile : $this->infoFile);
	}
	
	public function __construct($infoFile, $errorFile) {
		$this->infoFile = $infoFile;
		$this->errorFile = $errorFile;
	}
}