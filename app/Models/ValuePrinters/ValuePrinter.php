<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Components\Badges\NotSetBadge;
use Nette\Utils\Html;

/**
 * @phpstan-template TValue
 */
abstract class ValuePrinter
{
    /**
     * @phpstan-param TValue|mixed $value
     */
    abstract protected function getHtml($value): Html;

    /**
     * @phpstan-param TValue|mixed $value
     */
    public function __invoke($value): Html
    {
        if (\is_null($value)) {
            return $this->getEmptyValueHtml();
        }
        return $this->getHtml($value);
    }

    protected function getEmptyValueHtml(): Html
    {
        return NotSetBadge::getHtml();
    }
}
