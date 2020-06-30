<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Models\ModelTaskContribution;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServiceTaskContribution;
use FKSDB\Submits\SeriesTable;
use FKSDB\YearCalculator;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;

class HandoutForm extends BaseComponent {
    const TASK_PREFIX = 'task';
    /** @var ServicePerson */
    private $servicePerson;
    /** @var YearCalculator */
    private $yearCalculator;
    /** @var SeriesTable */
    private $seriesTable;
    /** @var ServiceTaskContribution */
    private $serviceTaskContribution;
    /** @var PersonFactory */
    private $personFactory;

    /**
     * HandoutForm constructor.
     * @param Container $container
     * @param SeriesTable $seriesTable
     */
    public function __construct(Container $container, SeriesTable $seriesTable) {
        parent::__construct($container);
        $this->seriesTable = $seriesTable;
    }

    /**
     * @param PersonFactory $personFactory
     * @param ServicePerson $servicePerson
     * @param YearCalculator $yearCalculator
     * @param ServiceTaskContribution $serviceTaskContribution
     * @return void
     */
    public function injectPrimary(PersonFactory $personFactory, ServicePerson $servicePerson, YearCalculator $yearCalculator, ServiceTaskContribution $serviceTaskContribution) {
        $this->personFactory = $personFactory;
        $this->servicePerson = $servicePerson;
        $this->yearCalculator = $yearCalculator;
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentForm(): FormControl {
        $formControl = new FormControl();
        $form = $formControl->getForm();
        $orgProvider = new PersonProvider($this->servicePerson);
        $orgProvider->filterOrgs($this->seriesTable->getContest(), $this->yearCalculator);
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
     * @param Form $form
     * @throws AbortException
     */
    public function handleFormSuccess(Form $form) {
        $values = $form->getValues();

        $connection = $this->serviceTaskContribution->getConnection();

        $connection->beginTransaction();
        /** @var ModelTask $task */
        foreach ($this->seriesTable->getTasks() as $task) {
            $this->serviceTaskContribution->getTable()->where([
                'task_id' => $task->task_id,
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

        $this->getPresenter()->flashMessage(_('Přiřazení opravovatelů uloženo.'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'HandoutForm.latte');
        $this->template->render();
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    public function setDefaults() {
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
