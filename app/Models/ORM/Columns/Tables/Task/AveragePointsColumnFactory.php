<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Task;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\UI\NotSetBadge;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<TaskModel,never>
 */
final class AveragePointsColumnFactory extends AbstractColumnFactory
{
    /**
     * @param TaskModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        $avg = $model->getTaskStats()['averagePoints'];
        if (is_null($avg)) {
            return NotSetBadge::getHtml();
        }
        $max = $model->points;
        if ($avg > $max) {
            $color = '#00ff00';
        } else {
            $color = '#' .
                sprintf('%02X', intval(255 - (($avg / $max) * 255))) .
                sprintf('%02X', intval(($avg / $max) * 255)) .
                '00';
        }
        return Html::el('span')->addAttributes([
            'class' => 'badge',
            'style' => 'background-color:' . $color,
        ])->addText(round($avg, 2));
    }
}
