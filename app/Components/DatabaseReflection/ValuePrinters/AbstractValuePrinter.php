<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use Nette\Utils\Html;

/**
 * Class AbstractValuePrinter
 * *
 */
abstract class AbstractValuePrinter {
    /**
     * @param null $value
     * @return Html
     */
    abstract protected function getHtml($value): Html;

    /**
     * @param mixed $value
     * @return Html
     */
    public function __invoke($value): Html {
        return $this->getHtml($value);
    }
}
