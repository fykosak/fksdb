<?php

namespace FKSDB\Components\Controls\Helpers\Badges;

use FKSDB\ORM\Models\ModelContest;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Utils\Html;

/**
 * Class ContestBadge
 * @package FKSDB\Components\Controls\Stalking\Helpers
 *
 */
class ContestBadge extends Control {

    /**
     * @param $contestId
     * @throws BadRequestException
     */
    public function render($contestId) {
        $this->template->html = self::getHtml($contestId);
        $this->template->setFile(__DIR__ . '/Contest.latte');
        $this->template->render();
    }

    /**
     * @param int|ModelContest $contest
     * @return Html
     * @throws BadRequestException
     */
    public static function getHtml($contest) {
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
        throw new BadRequestException();
    }
}

