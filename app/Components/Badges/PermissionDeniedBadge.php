<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use Nette\Utils\Html;

class PermissionDeniedBadge extends Badge
{

    public static function getHtml(...$args): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText(_('Permissions denied'));
    }
}
