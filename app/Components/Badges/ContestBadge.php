<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use FKSDB\Models\Exceptions\ContestNotFoundException;
use FKSDB\Models\ORM\Models\ContestModel;
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
        if ($contest instanceof ContestModel) {
            $contestId = $contest->contest_id;
        }
        $component = Html::el('span');
        switch ($contestId) {
            case ContestModel::ID_FYKOS:
                return $component->addAttributes(['class' => 'badge bg-fykos'])->addText(_('FYKOS'));
            case ContestModel::ID_VYFUK:
                return $component->addAttributes(['class' => 'badge bg-vyfuk'])->addText(_('Výfuk'));
        }
        throw new ContestNotFoundException($contestId);
    }
}
