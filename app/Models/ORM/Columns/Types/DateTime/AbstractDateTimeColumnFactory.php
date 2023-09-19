<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types\DateTime;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\UI\DatePrinter;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template ArgType
 * @phpstan-extends ColumnFactory<TModel,ArgType>
 */
abstract class AbstractDateTimeColumnFactory extends ColumnFactory
{
    private DatePrinter $printer;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->printer = new DatePrinter($this->getDefaultFormat());
    }

    final public function setDataFormat(?string $format): void
    {
        $this->printer = new DatePrinter($format);
    }

    final protected function createHtmlValue(Model $model): Html
    {
        return ($this->printer)($model->{$this->modelAccessKey});
    }

    abstract protected function getDefaultFormat(): string;
}
