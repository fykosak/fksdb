<?php

namespace FKSDB\Components\React;

use FKSDB\Components\Controls\BaseComponent;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * Class ReactComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class ReactComponent2 extends BaseComponent {
    use ReactComponentTrait2;

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
     * @return void
     */
    final public function render() {
        $html = Html::el('div');
        $this->appendPropertyTo($html);
        $this->template->html = $html;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ReactComponent.latte');
        $this->template->render();
    }
}
