<?php

namespace FKSDB;

use mysql_xdevapi\Result;
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

    private string $payload;

    /**
     * FullHttpRequest constructor.
     * @param IRequest $request
     * @param mixed $payload
     */
    public function __construct(IRequest $request, string $payload) {
        $this->request = $request;
        $this->payload = $payload;
    }

    public function getRequest(): IRequest {
        return $this->request;
    }

    public function getPayload(): string {
        return $this->payload;
    }
}
