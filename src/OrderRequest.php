<?php
namespace AmazonMWS;

class OrderRequest extends AbstractRequest {
	public function getPath() {
		return 'MarketplaceWebServiceOrders';
	}

	public function getURL() {
		return 'https://mws.amazonservices.com/Orders/2013-09-01';
	}
}