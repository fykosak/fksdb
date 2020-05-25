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
     * @param $data
     * @return void
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @param Message[] $messages
     * @return void
     */
    public function setMessages(array $messages) {
        $this->messages = $messages;
    }

    /**
     * @param Message $message
     * @return void
     */
    public function addMessage(Message $message) {
        $this->messages[] = $message;
    }

    /**
     * @param string $act
     * @return void
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
