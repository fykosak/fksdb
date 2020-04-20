<?php

namespace FKSDB\Components\Controls\Badges;

use FKSDB\Exceptions\ContestNotFoundException;
use FKSDB\ORM\Models\ModelContest;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class ContestBadge
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
 */
class ContestBadge extends Badge {
    /**
     * @param mixed ...$args
     * @return Html
     * @throws ContestNotFoundException
     */
    public static function getHtml(...$args): Html {
        list($contest) = $args;
        $contestId = $contest;
        if ($contest instanceof ModelContest) {
            $contestId = $contest->contest_id;
        }
        $component = Html::el('span');
        switch ($contestId) {
            case ModelContest::ID_FYKOS:
                return $component->addAttributes(['class' => 'badge badge-fykos'])->addText(_('FYKOS'));
            case ModelContest::ID_VYFUK:
                return $component->addAttributes(['class' => 'badge badge-vyfuk'])->addText(_('Výfuk'));
        }
        throw new ContestNotFoundException($contestId);
    }
}

