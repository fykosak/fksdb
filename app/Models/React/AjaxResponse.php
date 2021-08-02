<?php

namespace FKSDB\Models\React;

use Nette\Application\Response;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\SmartObject;

final class AjaxResponse implements Response
{
    use SmartObject;

    private array $content = [];

    private int $code = 200;

    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    public function setContent(array $content): void
    {
        $this->content = $content;
    }

    public function send(IRequest $httpRequest, IResponse $httpResponse): void
    {
        $httpResponse->setCode($this->code);
        $httpResponse->setContentType($this->getContentType());
        $httpResponse->setExpiration(false);
        echo json_encode($this->content);
    }

    final public function getContentType(): string
    {
        return 'application/json';
    }
}
