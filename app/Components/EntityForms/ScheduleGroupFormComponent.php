<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @property ScheduleGroupModel|null $model
 */
class ScheduleGroupFormComponent extends EntityFormComponent
{

    public const CONTAINER = 'container';

    private ServiceScheduleGroup $serviceScheduleGroup;
    private EventModel $event;
    private SingleReflectionFormFactory $singleReflectionFormFactory;

    public function __construct(EventModel $event, Container $container, ?ScheduleGroupModel $model)
    {
        parent::__construct($container, $model);
        $this->event = $event;
    }

    final public function injectPrimary(
        ServiceScheduleGroup $serviceScheduleGroup,
        SingleReflectionFormFactory $singleReflectionFormFactory
    ): void {
        $this->serviceScheduleGroup = $serviceScheduleGroup;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull2($values[self::CONTAINER]);
        $data['event_id'] = $this->event->event_id;
        $model = $this->serviceScheduleGroup->storeModel($data, $this->model);
        $this->flashMessage(sprintf(_('Group "%s" has been saved.'), $model->getLabel()), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
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
        $container = $this->singleReflectionFormFactory->createContainer(
            'schedule_group',
            ['name_cs', 'name_en', 'start', 'end', 'schedule_group_type']
        );
        $form->addComponent($container, self::CONTAINER);
    }
}
