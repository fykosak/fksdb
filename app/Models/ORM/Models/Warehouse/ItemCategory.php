<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Warehouse;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class ItemCategory extends FakeStringEnum implements EnumColumn
{
    public const Apparel = 'apparel';
    public const Game = 'game';
    public const Book = 'book';
    public const Other = 'other';

    public function badge(): Html
    {
        $badge = 'badge bg-color-4';
        switch ($this->value) {
            case self::Apparel:
                $badge = 'badge bg-color-2';
                break;
            case self::Game:
                $badge = 'badge bg-color-1';
                break;
            case self::Book:
                $badge = 'badge bg-color-3';
                break;
        }
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::Game:
                return _('Game');
            case self::Apparel:
                return _('Apparel');
            case self::Book:
                return _('Book');
            case self::Other:
            default:
                return _('Other');
        }
    }

    public function title(): Title
    {
        return new Title(null, $this->label());
    }

    public static function cases(): array
    {
        return [
            new self(self::Apparel),
            new self(self::Game),
            new self(self::Book),
            new self(self::Other),
        ];
    }
}
