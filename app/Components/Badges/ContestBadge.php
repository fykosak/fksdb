<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use FKSDB\Models\ORM\Models\ContestModel;
use Nette\Http\IResponse;
use Nette\Utils\Html;

/**
 * @phpstan-extends Badge<ContestModel|int>
 */
class ContestBadge extends Badge
{
    public static function getHtml(...$args): Html
    {
        [$contest] = $args;
        if ($contest instanceof ContestModel) {
            $contestId = $contest->contest_id;
        } else {
            $contestId = (int)$contest;
        }
        $component = Html::el('span');
        switch ($contestId) {
            case ContestModel::ID_FYKOS:
                return $component->addAttributes(['class' => 'badge bg-fykos'])->addText(_('FYKOS'));
            case ContestModel::ID_VYFUK:
                return $component->addAttributes(['class' => 'badge bg-vyfuk'])->addText(_('Výfuk'));
            case 3:
                return $component->addAttributes(['class' => 'badge bg-ctyrboj'])->addText(_('Vědecký čtyřboj'));
        }
        throw new \InvalidArgumentException(sprintf(_('Contest %d not found'), $contestId), IResponse::S404_NOT_FOUND);
    }
}
