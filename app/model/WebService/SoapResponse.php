<?php

namespace FKSDB\WebService;

use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Tracy\Debugger;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SoapResponse implements \Nette\Application\IResponse {

    /** @var \SoapServer */
    private $soapServer;

    /**
     * SoapResponse constructor.
     * @param \SoapServer $server
     */
    public function __construct(\SoapServer $server) {
        $this->soapServer = $server;
    }

    public function send(IRequest $httpRequest, IResponse $httpResponse): void {
        try {
            $this->soapServer->handle();
        } catch (\Exception $e) {
            Debugger::log($e);
        }
    }

}
