<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

interface Place
{
    public static function fromPlace(string $place): self;

    public function xLayout(): float;

    public function yLayout(): float;

    public function sector(): string;

    public function sectorName(string $language): string;

    public function layout(): string;
}
