<?php

namespace FKSDB\Components\Controls\Helpers\Badges;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class ContestBadge
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
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
     * @param int $contestId
     * @return Html
     * @throws BadRequestException
     */
    public static function getHtml(int $contestId) {
        $component = Html::el('span');
        switch ($contestId) {
            case 1:
                return $component->addAttributes(['class' => 'badge badge-fykos'])->addText(_('FYKOS'));
            case 2:
                return $component->addAttributes(['class' => 'badge badge-vyfuk'])->addText(_('Výfuk'));
        }
        throw new BadRequestException();
    }
}

