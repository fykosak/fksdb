<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Models\DBReflection\OmittedControlException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Logging\ILogger;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\Models\Utils\FormUtils;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * Class ScheduleGroupFormComponent
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelScheduleGroup|null $model
 */
class ScheduleGroupFormComponent extends AbstractEntityFormComponent {

    public const CONTAINER = 'container';

    private ServiceScheduleGroup $serviceScheduleGroup;
    private ModelEvent $event;
    private SingleReflectionFormFactory $singleReflectionFormFactory;

    public function __construct(ModelEvent $event, Container $container, ?ModelScheduleGroup $model) {
        parent::__construct($container, $model);
        $this->event = $event;
    }

    final public function injectPrimary(ServiceScheduleGroup $serviceScheduleGroup, SingleReflectionFormFactory $singleReflectionFormFactory): void {
        $this->serviceScheduleGroup = $serviceScheduleGroup;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
    }

    protected function handleFormSuccess(Form $form): void {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values[self::CONTAINER], true);
        $data['event_id'] = $this->event->event_id;
        $model = $this->serviceScheduleGroup->store($this->model ?? null, $data);
        $this->flashMessage(sprintf(_('Group "%s" has been saved.'), $model->getLabel()), ILogger::SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(): void {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([
                self::CONTAINER => $this->model->toArray(),
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
        $container = $this->singleReflectionFormFactory->createContainer('schedule_group', ['name_cs', 'name_en', 'start', 'end', 'schedule_group_type']);
        $form->addComponent($container, self::CONTAINER);
    }
}
