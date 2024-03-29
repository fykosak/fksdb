<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use Nette\Utils\Html;

class NoRecordsBadge
{
    public static function getHtml(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'me-1 bg-warning badge'])->setText(_('No records'));
    }
}
