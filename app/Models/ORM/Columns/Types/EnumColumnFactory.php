<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

class EnumColumnFactory extends ColumnFactory
{
    /** @var EnumColumn|string */
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
    protected function createHtmlValue(Model $model): Html
    {
        $enum = $model->{$this->getModelAccessKey()};
        if (is_null($enum)) {
            return NotSetBadge::getHtml();
        }
        if (!$enum instanceof EnumColumn) {
            throw new BadTypeException(EnumColumn::class, $enum);
        }
        return $enum->badge();
    }
}
