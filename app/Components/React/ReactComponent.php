<?php

namespace FKSDB\Components\React;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Exceptions\BadTypeException;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\Utils\Html;
use Nette\Utils\JsonException;

/**
 * Class ReactComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class ReactComponent extends BaseComponent {

    use ReactComponentTrait;

    /**
     * ReactComponent constructor.
     * @param Container $container
     * @param string $reactId
     */
    public function __construct(Container $container, string $reactId) {
        parent::__construct($container);
        $this->registerReact($reactId);
    }

    /**
     * @param mixed ...$args
     * @return void
     * @throws JsonException
     */
    final public function render(...$args) {
        $html = Html::el('div');
        $this->appendPropertyTo($html, ...$args);
        $this->template->html = $html;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ReactComponent.latte');
        $this->template->render();
    }

    /**
     * @return IRequest
     * @throws BadTypeException
     */
    protected function getHttpRequest(): IRequest {
        $service = $this->getContext()->getByType(IRequest::class);
        if ($service instanceof IRequest) {
            return $service;
        }
        throw new BadTypeException(IRequest::class, $service);
    }

    /**
     * @return array
     * @throws BadTypeException
     */
    protected function getReactRequest(): array {
        $requestData = $this->getHttpRequest()->getPost('requestData');
        $act = $this->getHttpRequest()->getPost('act');
        return ['requestData' => $requestData, 'act' => $act];
    }
}
