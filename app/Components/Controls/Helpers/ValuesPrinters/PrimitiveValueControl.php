<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\ORM\AbstractModelSingle;

/**
 * Class SimpleValue
 * @package FKSDB\Components\Controls\Helpers\ValuePrinters
 */
abstract class PrimitiveValueControl extends AbstractValueControl {

    /**
     * @return string
     */
    protected function getTemplatePath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'PrimitiveValue.latte';
    }

    /**
     * @param AbstractModelSingle $model
     * @param string|null $title
     * @param string|null $accessKey
     * @param bool $hasPermissions
     */
    public function render(AbstractModelSingle $model, string $title = null, string $accessKey = null, bool $hasPermissions = true) {
        $this->beforeRender($title, $hasPermissions);
        $this->template->html = $this->getSafeHtml($model, $accessKey, $hasPermissions);
        $this->template->setFile($this->getTemplatePath());
        $this->template->render();
    }
}
