<?php
namespace AmazonMWS\log;

interface Logger {
	
	public function log($message, $error = false);
}
?>