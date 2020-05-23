<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\BaseComponent;
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
     * @param DetailFactory $detailFactory
     * @return void
     */
    public function injectDetailFactory(DetailFactory $detailFactory) {
        $this->detailFactory = $detailFactory;
    }

    public function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }

    /**
     * @param $section
     * @param $model
     * @return void
     */
    public function render($section, $model) {
        $this->template->data = $this->detailFactory->getSection($section);
        $this->template->model = $model;
        $this->template->setFile(__DIR__ . '/detail.latte');
        $this->template->render();
    }
}
