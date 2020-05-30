<?php

namespace FKSDB\Components\React;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Exceptions\BadTypeException;
use Nette\Application\BadRequestException;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class ReactComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class ReactComponent extends BaseComponent {

    use ReactField;

    /**
     * ReactComponent constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->registerMonitor();
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
        $service = $this->getContext()->getByType(IRequest::class);
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
}
