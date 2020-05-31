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

    public function setCode(int $code): void {
        $this->code = $code;
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function setData($data): void {
        $this->data = $data;
    }

    /**
     * @param Message[] $messages
     * @return void
     */
    public function setMessages(array $messages): void {
        $this->messages = $messages;
    }

    public function addMessage(Message $message): void {
        $this->messages[] = $message;
    }

    public function setAct(string $act): void {
        $this->act = $act;
    }

    /**
     * @param IRequest $httpRequest
     * @param IResponse $httpResponse
     * @throws JsonException
     */
    public function send(IRequest $httpRequest, IResponse $httpResponse): void {
        $httpResponse->setCode($this->code);
        $httpResponse->setContentType($this->getContentType());
        $httpResponse->setExpiration(false);
        $response = [
            'messages' => array_map(fn(Message $value) => $value->__toArray(), $this->messages),
            'act' => $this->act,
            'responseData' => $this->data,
        ];
        echo Nette\Utils\Json::encode($response);
    }
}
