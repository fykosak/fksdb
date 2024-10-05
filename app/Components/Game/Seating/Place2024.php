<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Seating;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\Localization\LangMap;
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
     * @phpstan-return array<self::Sector*,LangMap<'cs'|'en',string>>
     */
    public static function getSectors(): array
    {
        return [
            self::SectorRed => new LangMap(['en' => 'Red', 'cs' => 'Červený']),
            self::SectorGreen => new LangMap(['en' => 'Green', 'cs' => 'Zelený']),
            self::SectorBlue => new LangMap(['en' => 'Blue', 'cs' => 'Modrý']),
            self::SectorYellow => new LangMap(['en' => 'Yellow', 'cs' => 'Žlutý']),
            self::SectorMagenta => new LangMap(['en' => 'Magenta', 'cs' => 'Purpurová']),
            self::SectorDark => new LangMap(['en' => 'Black', 'cs' => 'Červný']),
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

    public function html(?TeamModel2 $team, ?string $dev): Html
    {
        $outerContainer = Html::el('g');
        $container = Html::el('g')
            ->addAttributes([
                'transform' => 'translate(' . $this->x() . ',' . $this->y() . ')',
                'data-sector' => $this->sector(),
            ]);
        $outerContainer->addHtml($container);
        $container->addHtml('<rect height="50" width="50" x="-25" y="-25"/>');
        if ($dev) {
            $container->addAttributes([
                'class' => 'seat',
                'data-dev' => $dev,
                'data-category' => $team ? $team->category->value : null,
                'data-lang' => $team ? $team->game_lang->value : null,
            ]);

            $container->addHtml(Html::el('text')->setText($team ? $team->fyziklani_team_id : $this->label()));
        } else {
            $container->addAttributes([
                'class' => $team ? 'seat seat-occupied' : 'seat',
            ]);
            $container->addHtml('<rect height="50" width="50" x="-25" y="-25"/>');

            if ($team) {
                $outerContainer->addHtml($this->arrow());
            }
            $container->addHtml(Html::el('text')->setText($this->label()));
        }

        return $outerContainer;
    }

    public function arrow(): Html
    {
        $polyline = [];
        if ($this->y() > 0) {
            $y = 125;
        } else {
            $y = -125;
        }
        $endArrow = Html::el('polyline')->addAttributes([
            'class' => 'end-arrow',
        ]);
        if ($y > $this->y()) {
            $endArrow->addAttributes([
                'points' => '-10,-5 10,-5 0,-30',
            ]);
        } else {
            $endArrow->addAttributes([
                'points' => '-10,5 10,5 0,30',
            ]);
        }
        $polyline[] = [-850, $y];
        if ($this->row > 12) {
            $polyline[] = [-100, $y];
            $polyline[] = [-100, $y < 0 ? -300 : 300];
            $polyline[] = [100, $y < 0 ? -300 : 300];
            $polyline[] = [100, $y];
        }
        $polyline[] = [$this->x(), $y];
        if ($y > $this->y()) {
            $polyline[] = [$this->x(), $y - 5];
        } else {
            $polyline[] = [$this->x(), $y + 5];
        }

        $polylineHtml = Html::el('polyline')->addAttributes([
            'class' => 'direction-line',
            'points' => join(" ", array_map(fn($point) => join(',', $point), $polyline)),
        ]);
        return Html::el('g')
            ->setAttribute('class', 'direction-arrow')
            ->addHtml($polylineHtml)
            ->addHtml($endArrow->setAttribute('transform', 'translate(' . $this->x() . ',' . $y . ')'));
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

    /**
     * @phpstan-param GettextTranslator<'cs'|'en'> $translator
     */
    public function sectorName(GettextTranslator $translator): string
    {
        return $translator->getVariant(self::getSectors()[$this->sector()]);
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
                if ($col === 'A' && $row > 12) {
                    continue;
                }
                if ($col === 'G' && $row === 23) {
                    continue;
                }
                if ($col === 'G' && $row === 24) {
                    continue;
                }
                if ($col === 'F' && $row === 23) {
                    continue;
                }
                if ($col === 'F' && $row === 24) {
                    continue;
                }
                if ($col === 'L' && $row === 6) {
                    continue;
                }
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
