<?php

declare(strict_types=1);

namespace FKSDB\Components\TeamSeating;

use Fykosak\Utils\Localization\LocalizedString;
use Nette\InvalidStateException;
use Nette\Utils\Html;

final class Place2024 implements Place
{
    public string $col;
    public int $row;

    // phpcs:disable
    private const SectorRed = 'R';
    private const SectorGreen = 'G';
    private const SectorBlue = 'B';
    private const SectorDark = 'D';
    private const SectorMagenta = 'M';
    private const SectorYellow = 'Y';

    // phpcs:enable

    public function __construct(int $row, string $col)
    {
        $this->row = $row;
        $this->col = $col;
    }

    public static function fromPlace(string $place): self
    {
        $matches = [];
        preg_match('/([0-9]+)([A-Z]+)/', $place, $matches);
        [, $row, $col] = $matches;
        return new self((int)$row, (string)$col);
    }

    /**
     * @phpstan-return array<self::Sector*,LocalizedString<'cs'|'en'>>
     */
    public static function getSectors(): array
    {
        return [
            self::SectorRed => new LocalizedString(['en' => 'Red', 'cs' => 'Červený']),
            self::SectorGreen => new LocalizedString(['en' => 'Green', 'cs' => 'Zelený']),
            self::SectorBlue => new LocalizedString(['en' => 'Blue', 'cs' => 'Modrý']),
            self::SectorYellow => new LocalizedString(['en' => 'Yellow', 'cs' => 'Žlutý']),
            self::SectorMagenta => new LocalizedString(['en' => 'Magenta', 'cs' => 'Purpurová']),
            self::SectorDark => new LocalizedString(['en' => 'Black', 'cs' => 'Červný']),
        ];
    }

    public function x(): float
    {
        $x = -750;
        $x += $this->row * 50;
        if ($this->row > 12) {
            $x += 250;
        }
        return $x;
    }

    public function y(): float
    {
        switch ($this->col) {
            case 'A':
                return 325;
            case 'B':
                return 275;
            case 'C':
                return 225;
            case 'D':
                return 175;
            case 'E':
                return 75;
            case 'F':
                return 25;
            case 'G':
                return -25;
            case 'H':
                return -75;
            case 'I':
                return -175;
            case 'J':
                return -225;
            case 'K':
                return -275;
            case 'L':
                return -325;
        }
        return 0;
    }

    /**
     * @phpstan-return self::Sector*
     */
    public function sector(): string
    {
        $sectors = [
            [self::SectorRed, self::SectorYellow, self::SectorBlue],
            [self::SectorGreen, self::SectorMagenta, self::SectorDark],
        ];
        if ($this->row < 13) {
            $x = 0;
        } else {
            $x = 1;
        }
        if (in_array($this->col, ['A', 'B', 'C', 'D'])) {
            $y = 0;
        } elseif (in_array($this->col, ['E', 'F', 'G', 'H'])) {
            $y = 1;
        } elseif (in_array($this->col, ['I', 'J', 'K', 'L'])) {
            $y = 2;
        } else {
            throw new InvalidStateException();
        }
        return $sectors[$x][$y];
    }

    public function sectorName(string $language): string
    {
        return self::getSectors()[$this->sector()]->getText($language); //@phpstan-ignore-line
    }

    public function layout(): string
    {
        return __DIR__ . '/Rooms/pva24.latte';
    }

    /**
     * @return self[]
     */
    public static function getAll(): array
    {
        $places = [];
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'] as $col) {
            for ($row = 1; $row <= 24; $row++) {
                $places[] = new self($row, $col);
            }
        }
        return $places;
    }

    public function label(): string
    {
        return $this->row . $this->col;
    }

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes([
                'class' => 'badge',
                'data-sector' => $this->sector(),
            ])
            ->addText($this->label());
    }

    public function __serialize(): array
    {
        return [
            'sector' => $this->sector(),
            'label' => $this->label(),
        ];
    }
}
