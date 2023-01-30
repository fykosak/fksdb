<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @property ScheduleItemModel|null $model
 */
class ScheduleItemFormContainer extends EntityFormComponent
{

    public const CONTAINER = 'container';

    private ScheduleItemService $scheduleItemService;
    private EventModel $event;
    private SingleReflectionFormFactory $singleReflectionFormFactory;

    public function __construct(EventModel $event, Container $container, ?ScheduleItemModel $model)
    {
        parent::__construct($container, $model);
        $this->event = $event;
    }

    final public function injectPrimary(
        ScheduleItemService $scheduleItemService,
        SingleReflectionFormFactory $singleReflectionFormFactory
    ): void {
        $this->scheduleItemService = $scheduleItemService;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull2($values[self::CONTAINER]);
        $data['event_id'] = $this->event->event_id;
        $model = $this->scheduleItemService->storeModel($data, $this->model);
        $this->flashMessage(sprintf(_('Item "#%d" has been saved.'), $model->schedule_item_id), Message::LVL_SUCCESS);
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
        $container = $this->singleReflectionFormFactory->createContainerWithMetadata('schedule_item', [
            'name_cs' => ['required' => true],
            'name_en' => ['required' => true],
            'description_cs' => ['required' => false],
            'description_en' => ['required' => false],
            'require_id_number' => ['required' => true],
            'capacity' => ['required' => false],
            'price_czk' => ['required' => false],
            'price_eur' => ['required' => false],
        ]);
        $items = [];
        /** @var ScheduleGroupModel $group */
        foreach ($this->event->getScheduleGroups() as $group) {
            $items[$group->schedule_group_id] = ($this->translator->lang === 'cs' ? $group->name_cs : $group->name_en)
                . '(' . $group->schedule_group_type->value . ')';
        }
        $container->addSelect('schedule_group_id', _('Schedule group Id'), $items);
        $form->addComponent($container, self::CONTAINER);
    }
}
