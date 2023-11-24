<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\UI\NotSetBadge;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<ScheduleItemModel>
 */
class CapacityProgress extends AbstractColumnFactory
{
    /**
     * @param ScheduleItemModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (is_null($model->capacity)) {
            return NotSetBadge::getHtml();
        }
        $capacity = $model->capacity;
        $used = $model->getUsedCapacity();
        $percent = 100 * ($used / $capacity);
        $progress = Html::el('div')->setAttribute('class', 'progress-stacked');
        $bar1 = Html::el('div')->addAttributes([
            'class' => 'progress',
            'role' => 'progressbar',
            'style' => 'width:' . $percent . '%',
        ]);
        $bar1->addHtml(Html::el('div')->setAttribute('class', 'progress-bar bg-danger')->addText($used));
        $progress->addHtml($bar1);
        $bar2 = Html::el('div')->addAttributes([
            'class' => 'progress',
            'role' => 'progressbar',
            'style' => 'width:' . (100 - $percent) . '%',
        ]);
        $bar2->addHtml(Html::el('div')->setAttribute('class', 'progress-bar bg-success')->addText($capacity - $used));
        $progress->addHtml($bar2);
        return $progress;
    }
}
