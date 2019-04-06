<?php


namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\ORM\AbstractModelSingle;

/**
 * Class DateValueControl
 * @package FKSDB\Components\Controls\Helpers\ValuePrinters
 */
class DateValueControl extends PrimitiveValue {
    /**
     * @return string
     */
    protected function getTemplatePath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'DateValue.latte';
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $title
     * @param string $accessKey
     * @param bool $hasPermissions
     * @param string $format
     */
    public function render(AbstractModelSingle $model, string $title, string $accessKey, bool $hasPermissions = true, string $format = 'c') {
        $this->template->format = $format;
        parent::render($model, $title, $accessKey, $hasPermissions);
    }
}
