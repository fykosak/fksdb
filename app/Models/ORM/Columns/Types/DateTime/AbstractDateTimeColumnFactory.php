<?php

namespace FKSDB\Models\ORM\Columns\Types\DateTime;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\DatePrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class AbstractDateTimeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractDateTimeColumnFactory extends ColumnFactory {

    private string $format;

    final public function setFormat(string $format): void {
        $this->format = $format;
    }

    final protected function createHtmlValue(AbstractModelSingle $model): Html {
        return (new DatePrinter($this->format ?? $this->getDefaultFormat()))($model->{$this->getModelAccessKey()});
    }

    abstract protected function getDefaultFormat(): string;
}
