<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team\Seating;

use Fykosak\Utils\Localization\LocalizedString;
use Nette\Utils\Html;

final class Place2022 implements Place
{
    public int $xLayout;
    public int $yLayout;
    public string $sector;
    public string $row;
    public string $col;

    public function __construct(string $place)
    {
        [$sector, $row, $col, $xLayout, $yLayout] = explode(',', $place);
        $this->yLayout = (int)$yLayout;
        $this->xLayout = (int)$xLayout;
        $this->sector = $sector;
        $this->row = $row;
        $this->col = $col;
    }

    public static function fromPlace(string $place): self
    {
        return new self($place);
    }

    /**
     * @phpstan-return LocalizedString<'cs'|'en'>[]
     */
    public static function getSectors(): array
    {
        return [
            'A' => new LocalizedString(['cs' => 'Zelený', 'en' => 'Green']),
            'B' => new LocalizedString(['cs' => 'Modrý', 'en' => 'Blue']),
            'C' => new LocalizedString(['cs' => 'Žlutý', 'en' => 'Yellow']),
            'D' => new LocalizedString(['cs' => 'Červený', 'en' => 'Red']),
        ];
    }

    public function x(): float
    {
        return $this->xLayout;
    }

    public function y(): float
    {
        return $this->yLayout;
    }

    public function sector(): string
    {
        return $this->sector;
    }

    public function sectorName(string $language): string
    {
        return self::getSectors()[$this->sector]->getText($language); //@phpstan-ignore-line
    }

    public function layout(): string
    {
        return ''; //TODO
    }

    public function label(): string
    {
        return $this->row . $this->col;
    }

    public function badge(): Html
    {
        return Html::el('span');
    }

    public function __serialize(): array
    {
        return [
            'sector' => $this->sector(),
            'label' => $this->label(),
        ];
    }
}
