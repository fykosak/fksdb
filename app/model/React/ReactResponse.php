<?php

use Nette\SmartObject;

/**
 * Class ReactResponse
 */
final class ReactResponse implements Nette\Application\IResponse {
    use SmartObject;
    /**
     * @var ReactMessage[]
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
     * @return string
     */
    final public function getContentType(): string {
        return 'application/json';
    }

    /**
     * @param $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @param ReactMessage[] $messages
     */
    public function setMessages(array $messages) {
        $this->messages = $messages;
    }

    /**
     * @param ReactMessage $message
     */
    public function addMessage(\ReactMessage $message) {
        $this->messages[] = $message;
    }

    /**
     * @param string $act
     */
    public function setAct(string $act) {
        $this->act = $act;
    }

    /**
     * @param \Nette\Http\IRequest $httpRequest
     * @param \Nette\Http\IResponse $httpResponse
     * @throws \Nette\Utils\JsonException
     */
    public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse) {
        $httpResponse->setContentType($this->getContentType());
        $httpResponse->setExpiration(FALSE);
        $response = [
            'messages' => array_map(function (ReactMessage $value) {
                return $value->__toArray();
            }, $this->messages),
            'act' => $this->act,
            'responseData' => $this->data,
        ];
        echo Nette\Utils\Json::encode($response);
    }
}
