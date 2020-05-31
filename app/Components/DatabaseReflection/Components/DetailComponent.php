<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\ORM\AbstractModelSingle;

class DetailComponent extends BaseComponent {

    private DetailFactory $detailFactory;

    public function injectDetailFactory(DetailFactory $detailFactory): void {
        $this->detailFactory = $detailFactory;
    }

    public function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }

    public function render(string $section, AbstractModelSingle $model): void {
        $this->template->data = $this->detailFactory->getSection($section);
        $this->template->model = $model;
        $this->template->setFile(__DIR__ . '/detail.latte');
        $this->template->render();
    }
}
