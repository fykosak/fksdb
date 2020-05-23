<?php

namespace FKSDB\Components\Controls\Badges;

use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class NoRecordsBadge
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NoRecordsBadge extends Badge {

    public static function getHtml(...$args): Html {
        return Html::el('span')->addAttributes(['class' => 'badge-warning badge'])->setText(_('No records'));
    }
}
