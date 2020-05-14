<?php

namespace FKSDB\Components\React;

use FKSDB\Exceptions\BadTypeException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\Templating\FileTemplate;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class ReactComponent
 * @property FileTemplate template
 *
 */
abstract class ReactComponent extends Control {

    use ReactField;
    /**
     * @var Container
     */
    protected $container;

    /**
     * ReactComponent constructor.
     * @param Container $context
     */
    public function __construct(Container $context) {
        parent::__construct();
        $this->container = $context;
    }

    /**
     * @param IComponent $obj
     */
    protected function attached($obj) {
        $this->attachedReact($obj);
        parent::attached($obj);
    }

    /**
     * @throws JsonException
     */
    final public function render() {
        $this->configure();
        $this->template->reactId = $this->getReactId();
        $this->template->actions = Json::encode($this->actions);
        $this->template->data = $this->getData();

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ReactComponent.latte');
        $this->template->render();
    }

    /**
     * @return IRequest
     * @throws BadRequestException
     */
    protected function getHttpRequest(): IRequest {
        $service = $this->container->getByType(IRequest::class);
        if ($service instanceof IRequest) {
            return $service;
        }
        throw new BadTypeException(IRequest::class, $service);
    }

    /**
     * @return object
     * @throws BadRequestException
     */
    protected function getReactRequest() {

        $requestData = $this->getHttpRequest()->getPost('requestData');
        $act = $this->getHttpRequest()->getPost('act');
        return (object)['requestData' => $requestData, 'act' => $act];
    }

    /**
     * @return Container
     */
    final public function getContext() {
        return $this->container;
    }
}
