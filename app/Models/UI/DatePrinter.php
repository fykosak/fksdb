<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use Nette\Utils\Html;

class DatePrinter
{
    protected string $format;

    public function __construct(string $format)
    {
        $this->format = $format;
    }

    public function __invoke(?\DateTimeInterface $value): Html
    {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addText($value->format($this->format));
    }
}
