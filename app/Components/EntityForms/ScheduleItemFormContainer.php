<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleItem;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @property ModelScheduleItem|null $model
 */
class ScheduleItemFormContainer extends AbstractEntityFormComponent
{

    public const CONTAINER = 'container';

    private ServiceScheduleItem $serviceScheduleItem;
    private ModelEvent $event;
    private SingleReflectionFormFactory $singleReflectionFormFactory;

    public function __construct(ModelEvent $event, Container $container, ?ModelScheduleItem $model)
    {
        parent::__construct($container, $model);
        $this->event = $event;
    }

    final public function injectPrimary(
        ServiceScheduleItem $serviceScheduleItem,
        SingleReflectionFormFactory $singleReflectionFormFactory
    ): void {
        $this->serviceScheduleItem = $serviceScheduleItem;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values[self::CONTAINER], true);
        $data['event_id'] = $this->event->event_id;
        /** @var ModelScheduleItem $model */
        $model = $this->serviceScheduleItem->storeModel($data, $this->model);
        $this->flashMessage(sprintf(_('Item "%s" has been saved.'), $model->getLabel()), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('ScheduleGroup:detail', ['id' => $model->schedule_group_id]);
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void
    {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([
                self::CONTAINER => $this->model->toArray(),
            ]);
        }
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
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
