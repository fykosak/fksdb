<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use FKSDB\Models\ORM\Models\CountryModel;
use Nette\Utils\Html;

class FlagBadge
{
    public static function getHtml(CountryModel $country): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'mx-2'])
            ->addHtml(
                Html::el('i')
                    ->addAttributes([
                        'title' => $country->name,
                        'class' => 'flag-icon flag-icon-' . \strtolower($country->alpha_2),
                    ])
            );
    }
}
