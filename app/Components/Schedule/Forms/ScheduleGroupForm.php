<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Forms;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleGroupService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<ScheduleGroupModel>
 */
class ScheduleGroupForm extends EntityFormComponent
{
    public const CONTAINER = 'container';

    private ScheduleGroupService $scheduleGroupService;
    private EventModel $event;

    public function __construct(EventModel $event, Container $container, ?ScheduleGroupModel $model)
    {
        parent::__construct($container, $model);
        $this->event = $event;
    }

    final public function injectPrimary(ScheduleGroupService $scheduleGroupService): void
    {
        $this->scheduleGroupService = $scheduleGroupService;
    }

    protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var array{container:array{
         *         name_cs:string,
         *         name_en:string,
         *         start:\DateTimeInterface,
         *         end:\DateTimeInterface,
         *         schedule_group_type:string,
         *         registration_begin:\DateTimeInterface,
         *         registration_end:\DateTimeInterface,
         *         modification_end:\DateTimeInterface,
         * }} $values
         */
        $values = $form->getValues('array');
        $data = FormUtils::emptyStrToNull2($values[self::CONTAINER]);
        $data['event_id'] = $this->event->event_id;
        $model = $this->scheduleGroupService->storeModel($data, $this->model);
        $this->flashMessage(sprintf(_('Group "#%d" has been saved.'), $model->schedule_group_id), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('detail', ['id' => $model->getPrimary()]);
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([
                self::CONTAINER => $this->model->toArray(),
            ]);
        }
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'schedule_group');
        $container->addField('name_cs', ['required' => true]);
        $container->addField('name_en', ['required' => true]);
        $container->addField('start', ['required' => true]);
        $container->addField('end', ['required' => true]);
        $container->addField('schedule_group_type', ['required' => true]);
        $container->addField('registration_begin', []);
        $container->addField('registration_end', []);
        $container->addField('modification_end', []);
        $form->addComponent($container, self::CONTAINER);
    }
}
