<?php

namespace FKSDB\React;

use FKSDB\Messages\Message;
use Nette;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Class FKSDB\React\ReactResponse
 */
final class ReactResponse implements Nette\Application\IResponse {

    use SmartObject;
    /**
     * @var Message[]
     */
    private $messages = [];

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string
     */
    private $act;
    /**
     * @var int
     */
    private $code = 200;

    /**
     * @return string
     */
    final public function getContentType(): string {
        return 'application/json';
    }

    /**
     * @param int $code
     */
    public function setCode(int $code) {
        $this->code = $code;
    }

    /**
     * @param $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @param Message[] $messages
     */
    public function setMessages(array $messages) {
        $this->messages = $messages;
    }

    /**
     * @param Message $message
     */
    public function addMessage(Message $message) {
        $this->messages[] = $message;
    }

    /**
     * @param string $act
     */
    public function setAct(string $act) {
        $this->act = $act;
    }

    /**
     * @param IRequest $httpRequest
     * @param IResponse $httpResponse
     * @throws JsonException
     */
    public function send(IRequest $httpRequest, IResponse $httpResponse) {
        $httpResponse->setCode($this->code);
        $httpResponse->setContentType($this->getContentType());
        $httpResponse->setExpiration(FALSE);
        $response = [
            'messages' => array_map(function (Message $value) {
                return $value->__toArray();
            }, $this->messages),
            'act' => $this->act,
            'responseData' => $this->data,
        ];
        echo Nette\Utils\Json::encode($response);
    }
}
