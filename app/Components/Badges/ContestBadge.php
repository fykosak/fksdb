<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use FKSDB\Models\Exceptions\ContestNotFoundException;
use FKSDB\Models\ORM\Models\ModelContest;
use Nette\Utils\Html;

class ContestBadge extends Badge
{
    /**
     * @throws ContestNotFoundException
     */
    public static function getHtml(...$args): Html
    {
        [$contest] = $args;
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
