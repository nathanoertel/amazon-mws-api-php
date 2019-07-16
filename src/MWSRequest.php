<?php
namespace AmazonMWS;

class MWSRequest extends AbstractRequest {
	public function getPath() {
		return 'MarketplaceWebService';
	}

	public function getURL() {
		return 'https://mws.amazonservices.com';
	}
}