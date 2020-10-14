<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\DBReflection\OmittedControlException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\ORM\Services\Schedule\ServiceScheduleItem;
use FKSDB\Utils\FormUtils;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * Class ScheduleGroupFormComponent
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelScheduleItem|null $model
 */
class ScheduleItemFormContainer extends EditEntityFormComponent {

    public const CONTAINER = 'container';

    private ServiceScheduleItem $serviceScheduleItem;
    private ModelEvent $event;
    private SingleReflectionFormFactory $singleReflectionFormFactory;

    public function __construct(ModelEvent $event, Container $container, bool $create) {
        parent::__construct($container, $create);
        $this->event = $event;
    }

    final public function injectPrimary(ServiceScheduleItem $serviceScheduleItem, SingleReflectionFormFactory $singleReflectionFormFactory): void {
        $this->serviceScheduleItem = $serviceScheduleItem;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
    }

    protected function handleFormSuccess(Form $form): void {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values[self::CONTAINER], true);
        $data['event_id'] = $this->event->event_id;
        $model = $this->serviceScheduleItem->store($this->model ?? null, $data);
        $this->flashMessage(sprintf(_('Item "%s" has been saved.'), $model->getLabel()), ILogger::SUCCESS);
        $this->getPresenter()->redirect('ScheduleGroup:detail', ['id' => $model->schedule_group_id]);
    }

    /**
     * @param AbstractModelSingle|null $model
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(?AbstractModelSingle $model): void {
        if (!is_null($model)) {
            $this->getForm()->setDefaults([
                self::CONTAINER => $model->toArray(),
            ]);
        }
    }

    /**
     * @param Form $form
     * @return void
     * @throws BadTypeException
     * @throws AbstractColumnException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void {

        $container = $this->singleReflectionFormFactory->createContainer('schedule_item', [
            'name_cs',
            'name_en',
            'description_cs',
            'description_en',
            'require_id_number',
            'capacity',
            'price_czk',
            'price_eur',
        ]);
        $items = [];
        foreach ($this->event->getScheduleGroups() as $row) {
            $group = ModelScheduleGroup::createFromActiveRow($row);
            $items[$group->schedule_group_id] = $group->getLabel() . '(' . $group->schedule_group_type . ')';
        }
        $container->addSelect('schedule_group_id', _('Schedule group Id'), $items);
        $form->addComponent($container, self::CONTAINER);
    }
}