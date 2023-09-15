<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use FKSDB\Models\ORM\Models\ContestModel;
use Nette\Http\IResponse;
use Nette\Utils\Html;

class ContestBadge
{
    public static function getHtml(ContestModel $contest): Html
    {
        $component = Html::el('span');
        switch ($contest->contest_id) {
            case ContestModel::ID_FYKOS:
                return $component->addAttributes(['class' => 'badge bg-fykos'])->addText(_('FYKOS'));
            case ContestModel::ID_VYFUK:
                return $component->addAttributes(['class' => 'badge bg-vyfuk'])->addText(_('Výfuk'));
            case 3:
                return $component->addAttributes(['class' => 'badge bg-ctyrboj'])->addText(_('Vědecký čtyřboj'));
        }
        throw new \InvalidArgumentException(
            sprintf(_('Contest %d not found'), $contest->contest_id),
            IResponse::S404_NOT_FOUND
        );
    }
}
