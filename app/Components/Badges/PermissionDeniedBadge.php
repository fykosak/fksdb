<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use Nette\Utils\Html;

class PermissionDeniedBadge extends Badge
{
    /**
     * @phpstan-param never $args
     */
    public static function getHtml(...$args): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-danger'])->addText(_('Permissions denied'));
    }
}
