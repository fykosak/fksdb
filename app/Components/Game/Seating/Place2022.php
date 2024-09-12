<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Seating;

use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\Localization\LangMap;
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
     * @phpstan-return LangMap<'cs'|'en',string>[]
     */
    public static function getSectors(): array
    {
        return [
            'A' => new LangMap(['cs' => 'Zelený', 'en' => 'Green']),
            'B' => new LangMap(['cs' => 'Modrý', 'en' => 'Blue']),
            'C' => new LangMap(['cs' => 'Žlutý', 'en' => 'Yellow']),
            'D' => new LangMap(['cs' => 'Červený', 'en' => 'Red']),
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

    /**
     * @phpstan-param GettextTranslator<'cs'|'en'> $translator
     */
    public function sectorName(GettextTranslator $translator): string
    {
        return $translator->getVariant(self::getSectors()[$this->sector()]);
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
