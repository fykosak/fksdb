<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Types\DateTime;

use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\ValuePrinters\DatePrinter;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class AbstractDateTimeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractDateTimeColumnFactory extends DefaultColumnFactory {
    private string $format;

    public function setFormat(string $format): void {
        $this->format = $format;
    }

    final protected function createHtmlValue(AbstractModelSingle $model): Html {
        $format = $this->format ?? $this->getDefaultFormat();
        return (new DatePrinter($format))($model->{$this->getModelAccessKey()});
    }

    abstract protected function getDefaultFormat(): string;
}
