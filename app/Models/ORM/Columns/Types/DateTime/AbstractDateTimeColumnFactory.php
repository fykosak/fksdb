<?php

namespace FKSDB\Models\ORM\Columns\Types\DateTime;

use FKSDB\Models\ORM\Columns\Types\DefaultColumnFactory;
use FKSDB\Models\ValuePrinters\DatePrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
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
