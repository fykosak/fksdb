<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Warehouse;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

final class ProductCategory extends FakeStringEnum implements EnumColumn
{
    public const APPAREL = 'apparel';
    public const GAME = 'game';
    public const GAME_EXTENSION = 'game-extension';
    public const BOOK = 'book';
    public const OTHER = 'other';

    public function badge(): Html
    {
        $badge = 'badge bg-color-4';
        switch ($this->value) {
            case self::APPAREL:
                $badge = 'badge bg-color-2';
                break;
            case self::GAME:
            case self::GAME_EXTENSION:
                $badge = 'badge bg-color-1';
                break;
            case self::BOOK:
                $badge = 'badge bg-color-3';
                break;
        }
        return Html::el('span')->addAttributes(['class' => $badge])->addText($this->label());
    }

    public function label(): string
    {
        switch ($this->value) {
            case self::GAME:
                return _('Game');
            case self::GAME_EXTENSION:
                return _('Game extension');
            case self::APPAREL:
                return _('Apparel');
            case self::BOOK:
                return _('Book');
            case self::OTHER:
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
        return []; // TODO
    }
}
