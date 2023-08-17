<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestCategoryModel;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Utils\Html;

/**
 * @phpstan-extends Badge<ContestCategoryModel|GettextTranslator>
 */
class ContestCategoryBadge extends Badge
{
    /**
     * @throws BadTypeException
     */
    public static function getHtml(...$args): Html
    {
        [$contestCategory, $translator] = $args;
        if (!$contestCategory instanceof ContestCategoryModel) {
            throw new BadTypeException(ContestCategoryModel::class, $contestCategory);
        }
        if (!$translator instanceof GettextTranslator) {
            throw new BadTypeException(GettextTranslator::class, $translator);
        }
        return Html::el('span')->addAttributes(
            ['class' => 'badge bg-category-' . mb_strtolower(str_replace('_', '-', $contestCategory->label))]
        )->addText($contestCategory->name->getText($translator->lang));
    }
}
