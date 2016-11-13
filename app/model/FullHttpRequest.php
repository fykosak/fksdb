<?php

use Nette\Http\Request;
use Nette\Object;

/**
 * Unfortunately Nette Http\Request doesn't make raw HTTP data accessible.
 * Thus we have this wrapper class.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class FullHttpRequest extends Object {

	/** @var Request  */
	private $request;

	/** @var string  */
	private $payload;

	function __construct(Request $request, $payload) {
		$this->request = $request;
		$this->payload = $payload;
	}

	function getRequest() {
		return $this->request;
	}

	function getPayload() {
		return $this->payload;
	}

}
