<?php
namespace AmazonMWS;

class FBAInventoryRequest extends AbstractRequest {
	public function getPath() {
		return 'FBAInventoryServiceMWS';
	}

	public function getURL() {
		return 'https://mws.amazonservices.com/FulfillmentInventory/2010-10-01';
	}
}