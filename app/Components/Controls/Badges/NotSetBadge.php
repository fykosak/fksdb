<?php

namespace FKSDB\Components\Controls\Badges;

use Nette\Utils\Html;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NotSetBadge extends Badge {

    public static function getHtml(...$args): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-warning'])->addText(_('Not set'));
    }
}
