<?php

namespace FKSDB\Components\Controls\Badges;

use Nette\Utils\Html;

/**
 * Class ContestBadge
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PermissionDeniedBadge extends Badge {

    public static function getHtml(...$args): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText(_('Permissions denied'));
    }
}
