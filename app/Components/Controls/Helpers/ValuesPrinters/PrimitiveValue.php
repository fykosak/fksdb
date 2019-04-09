<?php


namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\ORM\AbstractModelSingle;

/**
 * Class SimpleValue
 * @package FKSDB\Components\Controls\Helpers\ValuePrinters
 */
abstract class PrimitiveValue extends AbstractValue {
    /**
     * @return string
     */
    abstract protected function getTemplatePath(): string;

    /**
     * @param AbstractModelSingle $model
     * @param string $title
     * @param string $accessKey
     * @param bool $hasPermissions
     */
    public function render(AbstractModelSingle $model, string $title = null, string $accessKey = null, bool $hasPermissions = true) {
        $this->beforeRender($title, $hasPermissions);
        $this->template->model = $model;
        $this->template->accessKey = $accessKey;
        $this->template->setFile($this->getTemplatePath());
        $this->template->render();
    }
}
