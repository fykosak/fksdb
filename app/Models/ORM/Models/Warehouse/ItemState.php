<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Warehouse;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\Utils\Html;

class ItemState extends FakeStringEnum implements EnumColumn
{
    public const NEW = 'new';
    public const USED = 'used';
    public const UNPACKED = 'unpacked';
    public const DAMAGED = 'damaged';

    public function badge(): Html
    {
        $badge = 'badge bg-color-5';
        switch ($this->value) {
            case self::NEW:
                $badge = 'badge bg-color-3';
                break;
            case self::USED:
                $badge = 'badge bg-color-2';
                break;
            case self::UNPACKED:
                $badge = 'badge bg-color-1';
                break;
            case self::DAMAGED:
                $badge = 'badge bg-color-4';
                break;
        }
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::NEW:
                return _('New');
            case self::USED:
                return _('Used');
            case self::UNPACKED:
                return _('Unpacked');
            case self::DAMAGED:
                return _('Damaged');
            default:
                return _('Other');
        }
    }

    /**
     * @return self[]
     */
    public static function cases(): array
    {
        return [
            new self(self::NEW),
            new self(self::USED),
            new self(self::UNPACKED),
            new self(self::DAMAGED),
        ];
    }

    public function getBehaviorType(): string
    {
        throw new NotImplementedException();
    }
}
