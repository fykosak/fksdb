<?php

namespace FKSDB\React;

use Nette;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Class ReactResponse
 * @author Michal Červeňák <miso@fykos.cz>
 */
final class AjaxResponse implements Nette\Application\IResponse {

    use SmartObject;

    /** @var array */
    private $content = [];

    /** @var int */
    private $code = 200;

    final public function getContentType(): string {
        return 'application/json';
    }

    /**
     * @param int $code
     * @return void
     */
    public function setCode(int $code) {
        $this->code = $code;
    }

    /**
     * @param array $content
     * @return void
     */
    public function setContent(array $content) {
        $this->content = $content;
    }

    /**
     * @param IRequest $httpRequest
     * @param IResponse $httpResponse
     * @throws JsonException
     */
    public function send(IRequest $httpRequest, IResponse $httpResponse) {
        $httpResponse->setCode($this->code);
        $httpResponse->setContentType($this->getContentType());
        $httpResponse->setExpiration(false);
        echo Nette\Utils\Json::encode($this->content);
    }
}
