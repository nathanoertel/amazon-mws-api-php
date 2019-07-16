<?php
namespace AmazonMWS;

class ProductRequest extends AbstractRequest {
	public function getPath() {
		return 'MarketplaceWebServiceProducts';
	}

	public function getURL() {
		return 'https://mws.amazonservices.com/Products/2011-10-01';
	}
}