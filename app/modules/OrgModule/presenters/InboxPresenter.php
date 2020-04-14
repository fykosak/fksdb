<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Inbox\CorrectedControl;
use FKSDB\Components\Controls\Inbox\SubmitsPreviewControl;
use FKSDB\Components\Controls\Inbox\SubmitCheckControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Controls\Inbox\InboxControl;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Models\ModelTaskContribution;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServiceTaskContribution;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Security\Permission;

/**
 * Class InboxPresenter
 * @package OrgModule
 */
class InboxPresenter extends SeriesPresenter {

    const TASK_PREFIX = 'task';

    /**
     * @var ServiceTaskContribution
     */
    private $serviceTaskContribution;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var SeriesTable
     */
    private $seriesTable;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @param ServiceTaskContribution $serviceTaskContribution
     */
    public function injectServiceTaskContribution(ServiceTaskContribution $serviceTaskContribution) {
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    /**
     * @param ServicePerson $servicePerson
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param SeriesTable $seriesTable
     */
    public function injectSeriesTable(SeriesTable $seriesTable) {
        $this->seriesTable = $seriesTable;
    }

    /**
     * @param PersonFactory $personFactory
     */
    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }
    /* ***************** AUTH ***********************/

    /**
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedInbox() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'list', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedHandout() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('task', 'edit', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedCorrected() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'corrected', $this->getSelectedContest()));
    }

    /* ***************** TITLES ***********************/
    public function titleInbox() {
        $this->setTitle(_('Inbox'), 'fa fa-envelope-open');
    }

    public function titleDefault() {
        $this->setTitle(_('Inbox dashboard'), 'fa fa-envelope-open');
    }

    public function titleHandout() {
        $this->setTitle(_('Rozdělení úloh opravovatelům'), 'fa fa-inbox');
    }

    public function titleList() {
        $this->setTitle(_('List of submits'), 'fa fa-cloud-download');
    }

    public function titleCorrected() {
        $this->setTitle(_('Corrected'), 'fa fa-inbox');
    }

    /* *********** LIVE CYCLE *************/
    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function startup() {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    public function actionHandout() {
        // This workaround fixes inproper caching of referenced tables.
        // $connection = $this->servicePerson->getConnection();
        // $connection->getCache()->clean(array(Cache::ALL => true));
        // $connection->getDatabaseReflection()->setConnection($connection);
    }

    /* ******************** RENDER ****************/

    /**
     * @throws BadRequestException
     */
    public function renderHandout() {
        $taskIds = [];
        foreach ($this->seriesTable->getTasks() as $row) {
            $task = ModelTask::createFromActiveRow($row);
            $taskIds[] = $task->task_id;
        }
        $contributions = $this->serviceTaskContribution->getTable()->where([
            'type' => ModelTaskContribution::TYPE_GRADE,
            'task_id' => $taskIds,
        ]);

        $values = [];
        foreach ($contributions as $row) {
            $contribution = ModelTaskContribution::createFromActiveRow($row);
            $taskId = $contribution->task_id;
            $personId = $contribution->person_id;
            $key = self::TASK_PREFIX . $taskId;
            if (!isset($values[$key])) {
                $values[$key] = [];
            }
            $values[$key][] = $personId;
        }
        /** @var FormControl $control */
        $control = $this->getComponent('handoutForm');
        $control->getForm()->setDefaults($values);

    }
    /* ******************* COMPONENTS ******************/
    /**
     * @return InboxControl
     */
    protected function createComponentInboxForm(): InboxControl {
        return new InboxControl($this->getContext(), $this->seriesTable);
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentHandoutForm() {
        $formControl = new FormControl();
        $form = $formControl->getForm();
        $orgProvider = new PersonProvider($this->servicePerson);
        $orgProvider->filterOrgs($this->getSelectedContest(), $this->yearCalculator);

        foreach ($this->seriesTable->getTasks() as $row) {
            $task = ModelTask::createFromActiveRow($row);
            $control = $this->personFactory->createPersonSelect(false, $task->getFQName(), $orgProvider);
            $control->setMultiSelect(true);
            $form->addComponent($control, self::TASK_PREFIX . $task->task_id);
        }

        $form->addSubmit('save', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->handoutFormSuccess($form);
        };

        return $formControl;
    }

    /**
     * @return CorrectedControl
     */
    public function createComponentCorrectedFormControl(): CorrectedControl {
        return new CorrectedControl($this->getContext(), $this->seriesTable);
    }

    /**
     * @return SubmitCheckControl
     */
    protected function createComponentCheckControl(): SubmitCheckControl {
        return new SubmitCheckControl($this->getContext(), $this->seriesTable);
    }

    /**
     * @return SubmitsPreviewControl
     */
    protected function createComponentSubmitsTableControl(): SubmitsPreviewControl {
        return new SubmitsPreviewControl($this->getContext(), $this->seriesTable);
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    public function handoutFormSuccess(Form $form) {
        $values = $form->getValues();

        $service = $this->serviceTaskContribution;
        $connection = $service->getConnection();

        $connection->beginTransaction();

        foreach ($this->seriesTable->getTasks() as $row) {
            $task = ModelTask::createFromActiveRow($row);
            $service->getTable()->where([
                'task_id' => $task->task_id,
                'type' => ModelTaskContribution::TYPE_GRADE
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

        $this->flashMessage(_('Přiřazení opravovatelů uloženo.'), self::FLASH_SUCCESS);
        $this->redirect('this');
    }

    /**
     * @return string
     */
    protected function getContainerClassNames(): string {
        switch ($this->getAction()) {
            case 'inbox':
                return str_replace('container ', 'container-fluid ', parent::getContainerClassNames());
            default:
                return parent::getContainerClassNames();
        }
    }
}
