<?php

use Nette\Http\Request;
use Nette\SmartObject;

/**
 * Unfortunately Nette Http\Request doesn't make raw HTTP data accessible.
 * Thus we have this wrapper class.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FullHttpRequest {

    use SmartObject;

    /** @var Request */
    private $request;

    /** @var string */
    private $payload;

    /**
     * FullHttpRequest constructor.
     * @param Request $request
     * @param $payload
     */
    function __construct(Request $request, $payload) {
        $this->request = $request;
        $this->payload = $payload;
    }

    /**
     * @return Request
     */
    function getRequest() {
        return $this->request;
    }

    /**
     * @return string
     */
    function getPayload() {
        return $this->payload;
    }

}
