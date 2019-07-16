<?php
namespace AmazonMWS;

use AmazonMWS\util\Array2XML;

class MWSRequest extends AbstractRequest {
	public function getReport($reportId) {
		$fileHandle = @fopen('php://memory', 'rw+');
		$request = array(
			'Report' => $fileHandle,
			'ReportId' => $reportId
		);

		$response = $this->sendRequest('MWS', 'getReport', $reportRequest);
		
		rewind($fileHandle);
		
		$responseStr = stream_get_contents($fileHandle);
		
		$this->log($responseStr);
		
		$responseXML = new SimpleXMLElement($responseStr);

		@fclose($fileHandle);

		return $responseXML;
	}

	public function submitFeed($feedType, $messageType, $messages, $feedRequest = array()) {
		$feed = array(
			'@attributes' => array(
				'xsi:noNamespaceSchemaLocation' => 'amzn-envelope.xsd',
				'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance'
			),
			'Header' => array(
				'DocumentVersion' => '1.01',
				'MerchantIdentifier' => $this->config['merchantId']
			),
			'MessageType' => $messageType,
			'Message' => $messages
		);
	

		$xml = Array2XML::createXML('AmazonEnvelope', $feed);
		
		return $this->submitFeedXML($feedType, $feedXML, $feedRequest);
	}

	public function submitFeedXML($feedType, $feedXML, $feedRequest = array()) {
		$this->log($feedXML->saveXML());

		$feedHandle = @fopen('php://memory', 'rw+');
		fwrite($feedHandle, $feedXML->saveXML());
		rewind($feedHandle);
		$md5 = base64_encode(md5(stream_get_contents($feedHandle), true));
		rewind($feedHandle);

		$request = array(
			'FeedType' => $feedType,
			'ContentMd5' => $md5,
			'PurgeAndReplace' => false,
			'FeedContent' => $feedHandle
		);

		$request = array_merge($request, $feedRequest);

		rewind($feedHandle);

		@fclose($feedHandle);

		return $this->request('submitFeed', $request);
	}

	public function getFeedSubmissionResult($feedSubmissionId) {
		$fileHandle = @fopen('php://memory', 'rw+');

		$request = array(
			'FeedSubmissionId' => $feedSubmissionId,
			'FeedSubmissionResult' => $fileHandle
		);

		$response = $this->request('getFeedSubmissionResult', $request);

		rewind($fileHandle);

		$responseStr = stream_get_contents($fileHandle);
		
		$this->log($responseStr);
		
		$responseXML = new SimpleXMLElement($responseStr);
		
		@fclose($fileHandle);

		return $responseXML;
	}

	public function getPath() {
		return 'MarketplaceWebService';
	}

	public function getURL() {
		return 'https://mws.amazonservices.com';
	}
}