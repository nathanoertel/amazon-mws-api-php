<?php
namespace AmazonMWS\sp;

class AuthorizationRequest extends AbstractRequest {
	public function getAuthorizationCode($sellingPartnerId, $developerId, $mwsAuthToken) {
		return $this->get('/authorizationCode', [
			'sellingPartnerId' => $sellingPartnerId,
			'developerId' => $developerId,
			'mwsAuthToken' => $mwsAuthToken
		]);
	}

	protected function getPath() {
		return '/authorization/v1';
	}
}