<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Utils\Html;

class ContestCategoryBadge
{
    public static function getHtml(ContestCategoryModel $contestCategory, GettextTranslator $translator): Html
    {
        return Html::el('span')->addAttributes(
            ['class' => 'badge bg-category-' . mb_strtolower(str_replace('_', '-', $contestCategory->label))]
        )->addText($contestCategory->name->getText($translator->lang));
    }
}
