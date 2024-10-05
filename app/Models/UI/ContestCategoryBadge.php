<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Utils\Html;

class ContestCategoryBadge
{
    /**
     * @phpstan-param GettextTranslator<'cs'|'en'> $translator
     */
    public static function getHtml(ContestCategoryModel $contestCategory, GettextTranslator $translator): Html
    {
        return Html::el('span')->addAttributes(
            ['class' => 'me-1 badge bg-category-' . mb_strtolower(str_replace('_', '-', $contestCategory->label))]
        )->addText($translator->getVariant($contestCategory->name));
    }
}
