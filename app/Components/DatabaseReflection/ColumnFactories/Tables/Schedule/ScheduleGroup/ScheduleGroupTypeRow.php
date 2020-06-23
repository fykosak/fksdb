<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleGroup;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class ScheduleGroupTypeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ScheduleGroupTypeRow extends AbstractColumnFactory {

    public function getTitle(): string {
        return _('Type');
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws NotImplementedException
     */
    public function createField(...$args): BaseControl {
        throw new NotImplementedException();
    }

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
