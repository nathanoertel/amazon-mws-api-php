<?php
namespace AmazonMWS;

class FinancesRequest extends AbstractRequest {
	public function getPath() {
		return 'MWSFinancesService';
	}

	public function getURL() {
		return 'https://mws.amazonservices.com/Finances/2015-05-01';
	}
}