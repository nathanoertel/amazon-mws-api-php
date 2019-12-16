<?php
namespace AmazonMWS;

class FBAOutboundRequest extends AbstractRequest {
	public function getPath() {
		return 'FBAOutboundServiceMWS';
	}

	public function getURL() {
		return 'https://mws.amazonservices.com/FulfillmentOutboundShipment/2010-10-01';
	}
}