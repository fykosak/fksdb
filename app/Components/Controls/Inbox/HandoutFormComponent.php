<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\ORM\Models\ModelTaskContribution;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\ORM\Services\ServiceTaskContribution;
use FKSDB\Models\Submits\SeriesTable;
use Nette\Application\UI\Form;
use Nette\DI\Container;

class HandoutFormComponent extends BaseComponent {

    public const TASK_PREFIX = 'task';

    private ServicePerson $servicePerson;

    private SeriesTable $seriesTable;

    private ServiceTaskContribution $serviceTaskContribution;

    private PersonFactory $personFactory;

    public function __construct(Container $container, SeriesTable $seriesTable) {
        parent::__construct($container);
        $this->seriesTable = $seriesTable;
    }

    final public function injectPrimary(PersonFactory $personFactory, ServicePerson $servicePerson, ServiceTaskContribution $serviceTaskContribution): void {
        $this->personFactory = $personFactory;
        $this->servicePerson = $servicePerson;
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentForm(): FormControl {
        $formControl = new FormControl($this->getContext());
        $form = $formControl->getForm();
        $orgProvider = new PersonProvider($this->servicePerson);
        $orgProvider->filterOrgs($this->seriesTable->getContestYear()->getContest());
        /** @var ModelTask $task */
        foreach ($this->seriesTable->getTasks() as $task) {
            $control = $this->personFactory->createPersonSelect(false, $task->getFQName(), $orgProvider);
            $control->setMultiSelect(true);
            $form->addComponent($control, self::TASK_PREFIX . $task->task_id);
        }

        $form->addSubmit('save', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleFormSuccess($form);
        };

        return $formControl;
    }

    /**
     * @throws ModelException
     */
    public function handleFormSuccess(Form $form): void {
        $values = $form->getValues();

        $connection = $this->serviceTaskContribution->explorer->getConnection();

        $connection->beginTransaction();
        /** @var ModelTask $task */
        foreach ($this->seriesTable->getTasks() as $task) {
            $task->related(DbNames::TAB_TASK_CONTRIBUTION)->where([
                'type' => ModelTaskContribution::TYPE_GRADE,
            ])->delete();
            $key = self::TASK_PREFIX . $task->task_id;
            foreach ($values[$key] as $personId) {
                $data = [
                    'task_id' => $task->task_id,
                    'person_id' => $personId,
                    'type' => ModelTaskContribution::TYPE_GRADE,
                ];
                $this->serviceTaskContribution->createNewModel($data);
            }
        }

        $connection->commit();

        $this->getPresenter()->flashMessage(_('Handout saved.'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }

    final public function render(): void {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.handout.latte');
    }

    /**
     * @throws BadTypeException
     */
    public function setDefaults(): void {
        $taskIds = [];
        /** @var ModelTask $task */
        foreach ($this->seriesTable->getTasks() as $task) {
            $taskIds[] = $task->task_id;
        }
        $contributions = $this->serviceTaskContribution->getTable()->where([
            'type' => ModelTaskContribution::TYPE_GRADE,
            'task_id' => $taskIds,
        ]);

        $values = [];
        /** @var ModelTaskContribution $contribution */
        foreach ($contributions as $contribution) {
            $taskId = $contribution->task_id;
            $personId = $contribution->person_id;
            $key = self::TASK_PREFIX . $taskId;
            if (!isset($values[$key])) {
                $values[$key] = [];
            }
            $values[$key][] = $personId;
        }
        /** @var FormControl $control */
        $control = $this->getComponent('form');
        $control->getForm()->setDefaults($values);
    }
}
