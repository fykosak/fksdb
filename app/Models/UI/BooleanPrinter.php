<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use Nette\Utils\Html;

class BooleanPrinter
{
    /**
     * @param int|bool|null $value
     */
    public static function getHtml($value): Html
    {
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        }
        if ($value) {
            return Html::el('span')->addAttributes(['class' => 'fas fa-check text-success']);
        } else {
            return Html::el('span')->addAttributes(['class' => 'fas fa-times text-danger']);
        }
    }
}
