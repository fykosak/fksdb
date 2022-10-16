<?php

declare(strict_types=1);

namespace FKSDB\Components\Badges;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestCategoryModel;
use Nette\Utils\Html;

class ContestCategoryBadge extends Badge
{
    /**
     * @throws BadTypeException
     */
    public static function getHtml(...$args): Html
    {
        [$contestCategory] = $args;
        if (!$contestCategory instanceof ContestCategoryModel) {
            throw new BadTypeException(ContestCategoryModel::class, $contestCategory);
        }
        return Html::el('span')->addAttributes(
            ['class' => 'badge bg-category-' . mb_strtolower(str_replace('_', '-', $contestCategory->label))]
        )->addText($contestCategory->label);
    }
}
