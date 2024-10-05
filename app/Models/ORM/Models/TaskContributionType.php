<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class TaskContributionType extends FakeStringEnum implements EnumColumn
{
    public const AUTHOR = 'author';
    public const SOLUTION = 'solution';
    public const GRADE = 'grade';

    public function behaviorType(): string
    {
        switch ($this->value) {
            case self::SOLUTION:
                return 'color-1';
            case self::GRADE:
            default:
                return 'color-2';
            case self::AUTHOR:
                return 'color-3';
        }
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-'.$this->behaviorType()])
            ->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::AUTHOR:
                return _('Author');
            case self::SOLUTION:
                return _('Solution');
            case self::GRADE:
            default:
                return _('Grade');
        }
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }

    public static function cases(): array
    {
        return [
            new self(self::AUTHOR),
            new self(self::SOLUTION),
            new self(self::GRADE),
        ];
    }
}
