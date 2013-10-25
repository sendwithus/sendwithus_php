<?php

namespace sendwithus;

/**
 * Send With Us PHP Client
 * @author sendwithus.com
 */

class API_Error extends \Exception
{
	public function __construct($message=null, $status=null, $body=null, $json=null)
	{
		parent::__construct($message);
		$this->status = $status;
		$this->body = $body;
		$this->json = $json;
	}

	public function getStatus() {
		return $this->status;
	}

	public function getBody() {
		return $this->body;
	}

	public function getJson() {
		return $this->json;
	}
}

?>