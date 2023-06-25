<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

class PersonGender extends FakeStringEnum implements EnumColumn
{
    public const MALE = 'M';
    public const FEMALE = 'F';

    public function badge(): Html
    {
        switch ($this->value) {
            case self::FEMALE:
                return Html::el('span')->addAttributes(['class' => 'fas fa-venus']);
            case self::MALE:
                return Html::el('span')->addAttributes(['class' => 'fas fa-mars']);
            default:
                return Html::el('span')->addAttributes(['class' => 'fas fa-transgender']);
        }
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::FEMALE:
                return _('Female');
            case self::MALE:
            default:
                return _('Male');
        }
    }

    public static function cases(): array
    {
        return [
            new static(self::MALE),
            new static(self::FEMALE),
        ];
    }

    public function getBehaviorType(): string
    {
        throw new NotImplementedException();
    }
}
