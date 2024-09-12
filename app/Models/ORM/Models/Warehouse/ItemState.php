<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Warehouse;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class ItemState extends FakeStringEnum implements EnumColumn
{
    public const NEW = 'new';
    public const USED = 'used';
    public const UNPACKED = 'unpacked';
    public const DAMAGED = 'damaged';

    public function behaviorType(): string
    {
        switch ($this->value) {
            case self::NEW:
                return 'color-3';
            case self::USED:
                return 'color-2';
            case self::UNPACKED:
                return 'color-1';
            case self::DAMAGED:
                return 'color-4';
            default:
                return 'color-5';
        }
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'badge bg-' . $this->behaviorType()])
            ->addText($this->label());
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
     * @phpstan-return self[]
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

    public function title(): Title
    {
        return new Title(null, $this->label());
    }
}
