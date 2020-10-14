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
use FKSDB\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\Utils\FormUtils;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * Class ScheduleGroupFormComponent
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelScheduleGroup|null $model
 */
class ScheduleGroupFormComponent extends EditEntityFormComponent {

    public const CONTAINER = 'container';

    private ServiceScheduleGroup $serviceScheduleGroup;
    private ModelEvent $event;
    private SingleReflectionFormFactory $singleReflectionFormFactory;

    public function __construct(ModelEvent $event, Container $container, bool $create) {
        parent::__construct($container, $create);
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
        $container = $this->singleReflectionFormFactory->createContainer('schedule_group', ['name_cs', 'name_en', 'start', 'end']);
        $container->addSelect('schedule_group_type', _('Schedule group type'), [
            'accommodation' => _('Accommodation'),
            'weekend' => _('Weekend'),
            'visa' => _('Visa'),
            'accommodation_gender' => _('Accommodation gender'),
            'accommodation_teacher' => _('Accommodation teacher'),
            'teacher_present' => _('Schedule during compotition'),
            'weekend_info' => _('Weekend info'),
        ]);
        $form->addComponent($container, self::CONTAINER);
    }
}