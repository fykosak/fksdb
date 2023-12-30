<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

use Fykosak\Utils\Localization\LocalizedString;

final class Place2022 implements Place
{
    public int $xLayout;
    public int $yLayout;
    public string $sector;

    public function __construct(int $xLayout, int $yLayout, string $sector)
    {
        $this->yLayout = $yLayout;
        $this->xLayout = $xLayout;
        $this->sector = $sector;
    }

    public static function fromPlace(string $place): self
    {
        [$sector, , , $xLayout, $yLayout] = explode(',', $place);
        return new self((int)$xLayout, (int)$yLayout, (string)$sector);
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

    public function xLayout(): float
    {
        return $this->xLayout;
    }

    public function yLayout(): float
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
}
