<?php

namespace FKSDB\Models\WebService;

use Nette\Application\Response;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Tracy\Debugger;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SoapResponse implements Response {

    private \SoapServer $soapServer;

    public function __construct(\SoapServer $server) {
        $this->soapServer = $server;
    }

    public function send(IRequest $httpRequest, IResponse $httpResponse): void {
        try {
            $this->soapServer->handle();
        } catch (\Throwable $e) {
            Debugger::log($e);
        }
    }
}
