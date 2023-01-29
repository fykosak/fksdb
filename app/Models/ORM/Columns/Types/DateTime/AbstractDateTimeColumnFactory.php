<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types\DateTime;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\DatePrinter;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

abstract class AbstractDateTimeColumnFactory extends ColumnFactory
{

    private string $format;

    final public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    final protected function createHtmlValue(Model $model): Html
    {
        return (new DatePrinter($this->format ?? $this->getDefaultFormat()))($model->{$this->modelAccessKey});
    }

    abstract protected function getDefaultFormat(): string;
}
