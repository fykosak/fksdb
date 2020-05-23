<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\BaseComponent;
use Nette\DI\Container;
use Nette\Templating\FileTemplate;

/**
 * Class DetailComponent
 * @package FKSDB\Components\DatabaseReflection
 * @property FileTemplate $template
 */
class DetailComponent extends BaseComponent {
    /**
     * @var DetailFactory
     */
    private $detailFactory;

    /**
     * DetailComponent constructor.
     * @param Container $container
     * @param DetailFactory $detailFactory
     */
    public function __construct(Container $container, DetailFactory $detailFactory) {
        parent::__construct($container);
        $this->detailFactory = $detailFactory;
    }

    /**
     * @return ValuePrinterComponent
     */
    public function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }

    /**
     * @param $section
     * @param $model
     */
    public function render($section, $model) {
        $this->template->data = $this->detailFactory->getSection($section);
        $this->template->model = $model;
        $this->template->setFile(__DIR__ . '/detail.latte');
        $this->template->render();
    }
}
