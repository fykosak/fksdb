<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\Schedule\ScheduleGroup;

use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Utils\Html;

/**
 * Class ScheduleGroupTypeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ScheduleGroupTypeRow extends DefaultColumnFactory {

    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $container = Html::el('span');
        switch ($model->schedule_group_type) {
            case ModelScheduleGroup::TYPE_ACCOMMODATION:
                $container->addAttributes(['class' => 'badge badge-1'])->addText(_('Accommodation'));
                break;
            case ModelScheduleGroup::TYPE_ACCOMMODATION_GENDER:
                $container->addAttributes(['class' => 'badge badge-2'])->addText(_('Accommodation gender'));
                break;
            case ModelScheduleGroup::TYPE_ACCOMMODATION_TEACHER:
                $container->addAttributes(['class' => 'badge badge-3'])->addText(_('Accommodation teacher'));
                break;
            case ModelScheduleGroup::TYPE_TEACHER_PRESENT:
                $container->addAttributes(['class' => 'badge badge-4'])->addText(_('Schedule during compotition'));
                break;
            case ModelScheduleGroup::TYPE_VISA:
                $container->addAttributes(['class' => 'badge badge-5'])->addText(_('Visa'));
                break;
            case ModelScheduleGroup::TYPE_WEEKEND:
                $container->addAttributes(['class' => 'badge badge-6'])->addText(_('Weekend'));
                break;
            case ModelScheduleGroup::TYPE_WEEKEND_INFO:
                $container->addAttributes(['class' => 'badge badge-7'])->addText(_('Weekend info'));
                break;
            default:
                $container->addAttributes(['class' => 'badge'])->addText(_($model->schedule_group_type));
                break;
        }
        return $container;
    }
}
