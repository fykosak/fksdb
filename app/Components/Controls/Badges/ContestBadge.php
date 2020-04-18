<?php

namespace FKSDB\Components\Controls\Badges;

use FKSDB\Exceptions\NotFoundException;
use FKSDB\ORM\Models\ModelContest;
use Nette\Application\BadRequestException;
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
     * @throws BadRequestException
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
        throw new NotFoundException(sprintf(_('Contest %d not found'), $contestId));
    }
}

