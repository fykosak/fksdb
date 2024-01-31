<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class TaskContributionType extends FakeStringEnum implements EnumColumn
{
    public const AUTHOR = 'author';
    public const SOLUTION = 'solution';
    public const GRADE = 'grade';

    public function badge(): Html
    {
        switch ($this->value) {
            case self::SOLUTION:
                $badge = 'badge bg-color-1';
                break;
            case self::GRADE:
            default:
                $badge = 'badge bg-color-2';
                break;
            case self::AUTHOR:
                $badge = 'badge bg-color-3';
                break;
        }
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
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

    public function getBehaviorType(): string
    {
        throw new NotImplementedException();
    }
}
