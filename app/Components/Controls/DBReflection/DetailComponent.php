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
    /** @var DetailFactory */
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
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR);
        $this->template->render();
    }
}
