<?php

namespace FKSDB\Model\React;

use Nette;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\SmartObject;

/**
 * Class ReactResponse
 * @author Michal Červeňák <miso@fykos.cz>
 */
final class AjaxResponse implements Nette\Application\IResponse {

    use SmartObject;

    private array $content = [];

    private int $code = 200;

    final public function getContentType(): string {
        return 'application/json';
    }

    public function setCode(int $code): void {
        $this->code = $code;
    }

    public function setContent(array $content): void {
        $this->content = $content;
    }

    public function send(IRequest $httpRequest, IResponse $httpResponse): void {
        $httpResponse->setCode($this->code);
        $httpResponse->setContentType($this->getContentType());
        $httpResponse->setExpiration(false);
        echo json_encode($this->content);
    }
}
