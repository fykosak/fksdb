<?php

namespace FKSDB;

use Nette\Http\IRequest;
use Nette\SmartObject;

/**
 * Unfortunately Nette Http\Request doesn't make raw HTTP data accessible.
 * Thus we have this wrapper class.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FullHttpRequest {

    use SmartObject;

    private IRequest $request;

    /** @var string */
    private $payload;

    /**
     * FullHttpRequest constructor.
     * @param IRequest $request
     * @param mixed $payload
     */
    public function __construct(IRequest $request, $payload) {
        $this->request = $request;
        $this->payload = $payload;
    }

    public function getRequest(): IRequest {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getPayload() {
        return $this->payload;
    }
}
