<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use FKSDB\Components\Badges\NotSetBadge;
use Nette\Utils\Html;

/**
 * @template TValue
 */
abstract class ValuePrinter
{
    /**
     * @param TValue $value
     */
    abstract protected function getHtml($value): Html;

    /**
     * @param TValue $value
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
