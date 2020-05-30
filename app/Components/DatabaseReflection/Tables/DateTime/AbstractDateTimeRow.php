<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class AbstractDateTimeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractDateTimeRow extends DefaultRow {
    /** @var string */
    private $format;

    /**
     * @param string $format
     * @return void
     */
    public function setFormat(string $format) {
        $this->format = $format;
    }

    final protected function createHtmlValue(AbstractModelSingle $model): Html {
        $format = $this->format ?: $this->getDefaultFormat();
        return (new DatePrinter($format))($model->{$this->getModelAccessKey()});
    }

    abstract protected function getDefaultFormat(): string;
}
