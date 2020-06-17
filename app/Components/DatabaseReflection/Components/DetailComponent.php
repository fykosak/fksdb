<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\ORM\AbstractModelSingle;

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

    protected function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }

    /**
     * @param string $section
     * @param AbstractModelSingle $model
     * @return void
     */
    public function render(string $section, AbstractModelSingle $model) {
        $this->template->data = $this->detailFactory->getSection($section);
        $this->template->model = $model;
        $this->template->setFile(__DIR__ . '/detail.latte');
        $this->template->render();
    }
}
