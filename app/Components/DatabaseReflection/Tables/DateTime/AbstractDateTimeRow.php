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

    private ?string $format = null;

    public function setFormat(string $format): void {
        $this->format = $format;
    }

    final protected function createHtmlValue(AbstractModelSingle $model): Html {
        $format = $this->format ?: $this->getDefaultFormat();
        return (new DatePrinter($format))($model->{$this->getModelAccessKey()});
    }

    abstract protected function getDefaultFormat(): string;
}
