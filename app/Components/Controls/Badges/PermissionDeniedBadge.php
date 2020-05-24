<?php

namespace FKSDB\Components\Controls\Badges;

use Nette\Utils\Html;

/**
 * Class ContestBadge
 * @package FKSDB\Components\Controls\Stalking\Helpers
 */
class PermissionDeniedBadge extends Badge {
    /**
     * @param array $args
     * @return Html
     */
    public static function getHtml(...$args): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText(_('Permissions denied'));
    }
}
