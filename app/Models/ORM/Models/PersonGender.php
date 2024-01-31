<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class PersonGender extends FakeStringEnum implements EnumColumn
{
    // phpcs:disable
    public const Male = 'M';
    public const Female = 'F';

    // phpcs:enable

    public function badge(): Html
    {
        return Html::el('span')->addAttributes(['class' => $this->iconClassName()]);
    }

    public function iconClassName(): string
    {
        switch ($this->value) {
            case self::Female:
                return 'fas fa-venus';
            case self::Male:
                return 'fas fa-mars';
            default:
                return 'fas fa-transgender';
        }
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::Female:
                return _('Female');
            case self::Male:
                return _('Male');
            default:
                return _('Other');
        }
    }

    public static function cases(): array
    {
        return [
            new self(self::Male),
            new self(self::Female),
        ];
    }

    public function getBehaviorType(): string
    {
        throw new NotImplementedException();
    }

    public function title(): Title
    {
        return new Title(null, $this->label(), $this->iconClassName());
    }
}
