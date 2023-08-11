<?php

declare(strict_types=1);

namespace FKSDB\Models\ValuePrinters;

use Nette\Utils\Html;

/**
 * @phpstan-extends ValuePrinter<\DateTimeInterface>
 */
class DatePrinter extends ValuePrinter
{
    protected string $format;

    public function __construct(string $format = 'c')
    {
        $this->format = $format;
    }

    /**
     * @param \DateTimeInterface $value
     */
    protected function getHtml($value): Html
    {
        return Html::el('span')->addText($value->format($this->format));
    }
}
