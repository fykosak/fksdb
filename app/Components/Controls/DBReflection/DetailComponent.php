<?php

namespace FKSDB\Components\Controls\DBReflection;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\DBReflection\DetailFactory;
use FKSDB\ORM\AbstractModelSingle;

/**
 * Class DetailComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DetailComponent extends BaseComponent {

    private DetailFactory $detailFactory;

    public function injectDetailFactory(DetailFactory $detailFactory): void {
        $this->detailFactory = $detailFactory;
    }

    protected function createComponentValuePrinter(): ValuePrinterComponent {
        return new ValuePrinterComponent($this->getContext());
    }

    public function render(string $section, AbstractModelSingle $model): void {
        $this->template->data = $this->detailFactory->getSection($section);
        $this->template->model = $model;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.detail.latte');
        $this->template->render();
    }
}
