<?php

declare(strict_types=1);

namespace FKSDB\Components\TeamSeating;

use Fykosak\Utils\Localization\LocalizedString;
use Nette\InvalidStateException;

final class Place2024 implements Place
{
    public string $col;
    public int $row;
    public string $sector;

    public function __construct(int $row, string $col)
    {
        $this->row = $row;
        $this->col = $col;
        $sectors = [['R', 'G', 'B'], ['Y', 'V', 'D']];
        if ($this->row < 14) {
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
        $this->sector = $sectors[$x][$y];
    }

    public static function fromPlace(string $place): self
    {
        $matches = [];
        preg_match('/([0-9]+)([A-Z]+)/', $place, $matches);
        [, $row, $col] = $matches;
        return new self((int)$row, (string)$col);
    }

    /**
     * @phpstan-return LocalizedString<'cs'|'en'>[]
     */
    public static function getSectors(): array
    {
        return [
            'R' => new LocalizedString(['en' => 'Red', 'cs' => 'Červený']),
            'G' => new LocalizedString(['en' => 'Green', 'cs' => 'Zelený']),
            'B' => new LocalizedString(['en' => 'Blue', 'cs' => 'Modrý']),
            'Y' => new LocalizedString(['en' => 'Yellow', 'cs' => 'Žlutý']),
            'V' => new LocalizedString(['en' => 'Violet', 'cs' => 'Fialový']),
            'D' => new LocalizedString(['en' => 'Dark', 'cs' => 'Tmavý']),
        ];
    }

    public function x(): float
    {
        $x = -800;
        $x += $this->row * 50;
        if ($this->row > 13) {
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
        return __DIR__ . '/Rooms/pva24.latte';
    }

    /**
     * @return self[]
     */
    public static function getAll(): array
    {
        $places = [];
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'] as $col) {
            for ($row = 1; $row <= 26; $row++) {
                $places[] = new self($row, $col);
            }
        }
        return $places;
    }

    public function label(): string
    {
        return $this->row . $this->col;
    }
}
