<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use Nette\Utils\Html;

class NoRecordsBadge extends Badge
{

    public static function getHtml(...$args): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge-warning badge'])->setText(_('No records'));
    }
}
