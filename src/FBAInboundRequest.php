<?php
namespace AmazonMWS;

class FBAInboundRequest extends AbstractRequest {
	public function getPath() {
		return 'FBAInboundServiceMWS';
	}

	public function getURL() {
		return 'https://mws.amazonservices.com/FulfillmentInboundShipment/2010-10-01';
	}
}