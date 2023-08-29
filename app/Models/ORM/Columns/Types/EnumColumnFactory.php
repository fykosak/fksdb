<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<Model,never>
 */
class EnumColumnFactory extends ColumnFactory
{

    /** @phpstan-var class-string<EnumColumn&FakeStringEnum> */
    private string $className;

    /**
     * @phpstan-param class-string<EnumColumn&FakeStringEnum> $className
     */
    public function setEnumClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @param never $args
     */
    protected function createFormControl(...$args): BaseControl
    {
        $items = [];
        foreach (($this->className)::cases() as $case) {
            $items[$case->value] = $case->label();
        }
        $control = new SelectBox($this->getTitle());
        $control->setItems($items);
        $control->setPrompt(_('--Select options--'));
        return $control;
    }

    /**
     * @throws BadTypeException
     */
    protected function createHtmlValue(Model $model): Html
    {
        $enum = $model->{$this->modelAccessKey};
        if (is_null($enum)) {
            return NotSetBadge::getHtml();
        }
        if (!$enum instanceof EnumColumn) {
            throw new BadTypeException(EnumColumn::class, $enum);
        }
        return $enum->badge();
    }
}
