<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use Nette\Templating\FileTemplate;

/**
 * Class BinaryValueControl
 * @property FileTemplate $template
 */
class PhoneValueControl extends PrimitiveValue {
    /**
     * @return string
     */
    protected function getTemplatePath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'PhoneNumber.latte';
    }
}
