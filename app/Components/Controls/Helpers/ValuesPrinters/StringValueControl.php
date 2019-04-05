<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use Nette\Templating\FileTemplate;

/**
 * Class BinaryValueControl
 * @property FileTemplate $template
 */
class StringValueControl extends PrimitiveValue {
    /**
     * @return string
     */
    protected function getTemplatePath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'StringValue.latte';
    }
}
