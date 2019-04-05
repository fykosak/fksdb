<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SoapResponse implements Nette\Application\IResponse {

    /**
     * @var SoapServer
     */
    private $soapServer;

    public function __construct(SoapServer $server) {
        $this->soapServer = $server;
    }

    public function send(\Nette\Http\IRequest $httpRequest, \Nette\Http\IResponse $httpResponse) {
        try {
            $this->soapServer->handle();
        } catch (Exception $e) {
            \Tracy\Debugger::log($e);
        }
    }

}
