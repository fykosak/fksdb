<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Seating;

use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Utils\Html;

interface Place
{
    public static function fromPlace(string $place): self;

    public function x(): float;

    public function y(): float;

    public function sector(): string;

    public function label(): string;

    /**
     * @phpstan-param GettextTranslator<'cs'|'en'> $translator
     */
    public function sectorName(GettextTranslator $translator): string;

    public function layout(): string;

    public function badge(): Html;

    /** @phpstan-return array{label:string,sector:string} */
    public function __serialize(): array;
}
