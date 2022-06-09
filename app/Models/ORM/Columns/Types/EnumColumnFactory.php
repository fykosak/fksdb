<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

class EnumColumnFactory extends ColumnFactory
{
    /** @var EnumColumn|string|enum */
    private string $className;

    public function setEnumClassName(string $className): void
    {
        $this->className = $className;
    }

    protected function createFormControl(...$args): BaseControl
    {
        $items = [];
        foreach (($this->className)::cases() as $case) {
            $items[$case->value] = $case->label();
        }
        $control = new SelectBox($this->getTitle());
        $control->setItems($items);
        return $control;
    }

    /**
     * @throws BadTypeException
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        $enum = $model->{$this->getModelAccessKey()};
        if (!$enum instanceof EnumColumn) {
            throw new BadTypeException(EnumColumn::class, $enum);
        }
        return $enum->badge();
    }
}
