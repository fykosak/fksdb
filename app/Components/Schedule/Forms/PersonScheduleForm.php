<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Forms;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonSelectBox;
use FKSDB\Components\Schedule\Input\ExistingPaymentException;
use FKSDB\Components\Schedule\Input\ScheduleException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<PersonScheduleModel,array{schedule_item_id:int,person_id:int}>
 */
final class PersonScheduleForm extends ModelForm
{
    private PersonScheduleService $personScheduleService;
    private ScheduleItemService $scheduleItemService;
    private PersonService $personService;
    private EventModel $event;

    public function __construct(EventModel $event, Container $container, ?PersonScheduleModel $model)
    {
        parent::__construct($container, $model);
        $this->event = $event;
    }

    final public function injectPrimary(
        PersonService $personService,
        ScheduleItemService $scheduleItemService,
        PersonScheduleService $personScheduleService
    ): void {
        $this->personService = $personService;
        $this->personScheduleService = $personScheduleService;
        $this->scheduleItemService = $scheduleItemService;
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults($this->model->toArray());
        }
    }

    protected function configureForm(Form $form): void
    {
        $personSelect = new PersonSelectBox(true, new PersonProvider($this->container));
        $personSelect->setRequired();
        $form->addComponent($personSelect, 'person_id');
        $items = [];
        /** @var ScheduleGroupModel $group */
        foreach ($this->event->getScheduleGroups() as $group) {
            if ($group->schedule_group_type->value === ScheduleGroupType::WeekendInfo) {
                continue;
            }
            $subItems = [];
            /** @var ScheduleItemModel $item */
            foreach ($group->getItems() as $item) {
                $subItems[$item->schedule_item_id] = $item->name->get($this->translator->lang);
            }
            $items[$group->name->get($this->translator->lang)] = $subItems;
        }
        $select = new SelectBox(_('Schedule item'), $items);
        $select->setRequired();
        $form->addComponent($select, 'schedule_item_id');
    }

    protected function innerSuccess(array $values, Form $form): PersonScheduleModel
    {
        $item = $this->scheduleItemService->findByPrimary($values['schedule_item_id']);
        $person = $this->personService->findByPrimary($values['person_id']);
        $oldPersonSchedule = $person->getScheduleByGroup($item->schedule_group);
        if ($oldPersonSchedule) {
            if (isset($this->model)) {
                if ($this->model->schedule_item_id !== $oldPersonSchedule->schedule_item_id) {
                    throw new ScheduleException($item->schedule_group, _('Already applied in this block'));
                }
            } else {
                throw new ScheduleException($item->schedule_group, _('Already applied in this block'));
            }
        }

        if (
            isset($this->model)
            && $this->model->getPayment()
            && $item->schedule_item_id !== $this->model->schedule_item_id
        ) {
            throw new ExistingPaymentException($this->model);
        }
        /** @var PersonScheduleModel $model */
        $model = $this->personScheduleService->storeModel($values, $this->model);
        return $model;
    }

    protected function successRedirect(Model $model): void
    {
        $this->flashMessage(_('Model has been saved.'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }
}
