<?php
namespace AmazonMWS;

class FBAOutboundRequest extends AbstractRequest {
	public function getPath() {
		return 'FBAOutboundServiceMWS';
	}

	public function getURL() {
		return 'http://mws.amazonservices.jp/FulfillmentOutboundShipment/2010-10-01';
	}
}