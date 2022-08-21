<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService;

use Nette\Application\Response;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\SmartObject;
use Tracy\Debugger;

class SoapResponse implements Response
{
    use SmartObject;

    private \SoapServer $soapServer;

    public function __construct(\SoapServer $server)
    {
        $this->soapServer = $server;
    }

    public function send(IRequest $httpRequest, IResponse $httpResponse): void
    {
        try {
            $this->soapServer->handle();
        } catch (\Throwable $e) {
            Debugger::log($e);
        }
    }
}
