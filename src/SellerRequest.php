<?php
namespace AmazonMWS;

class SellerRequest extends AbstractRequest {
	public function getPath() {
		return 'MarketplaceWebServiceSellers';
	}

	public function getURL() {
		return 'https://mws.amazonservices.com/Sellers/2011-07-01';
	}
}