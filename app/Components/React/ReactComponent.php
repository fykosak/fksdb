<?php

namespace FKSDB\Components\React;

use FKSDB\Components\Controls\BaseComponent;
use Nette\DI\Container;
use Nette\Utils\Html;

abstract class ReactComponent extends BaseComponent
{
    use ReactComponentTrait;

    public function __construct(Container $container, string $reactId)
    {
        parent::__construct($container);
        $this->registerReact($reactId);
    }

    final public function render(): void
    {
        $html = Html::el('div');
        $this->appendPropertyTo($html);
        $this->template->html = $html;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'ReactComponent.latte');
    }
}
