<?php

namespace FKSDB\Components\DatabaseReflection\ColumnFactories;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\Components\DatabaseReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class AbstractDateTimeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractDateTimeColumnFactory extends DefaultColumnFactory {
    /**
     * @var string
     */
    private $format;

    /**
     * @param string $format
     * @return void
     */
    public function setFormat(string $format) {
        $this->format = $format;
    }

    final protected function createHtmlValue(AbstractModelSingle $model): Html {
        $format = $this->format ?? $this->getDefaultFormat();
        return (new DatePrinter($format))($model->{$this->getModelAccessKey()});
    }

    abstract protected function getDefaultFormat(): string;
}
