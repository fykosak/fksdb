<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Trait DefaultPrinterTrait
 * @package FKSDB\Components\DatabaseReflection
 */
trait DefaultPrinterTrait {
    /**
     * @param AbstractModelSingle $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new StringPrinter())($model->{$this->getModelAccessKey()});
    }

    /**
     * @return string
     */
    abstract protected function getModelAccessKey(): string;
}
