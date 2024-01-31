<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\UI\NumberPrinter;

trait NumberFactoryTrait
{
    protected NumberPrinter $printer;

    /**
     * @phpstan-param NumberPrinter::NULL_* $nullValue
     */
    public function setNumberFactory(
        string $nullValue,
        ?string $prefix,
        ?string $suffix,
        int $decimalDigitsCount = 0
    ): void {
        $this->printer = new NumberPrinter($prefix, $suffix, $decimalDigitsCount, $nullValue);
    }
}
