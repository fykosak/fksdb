<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Inbox\CorrectedControl;
use FKSDB\Components\Controls\Inbox\SubmitsPreviewControl;
use FKSDB\Components\Controls\Inbox\SubmitCheckComponent;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Controls\Inbox\InboxControl;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Models\ModelTaskContribution;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServiceTaskContribution;
use FKSDB\UI\PageStyleContainer;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Security\Permission;

/**
 * Class InboxPresenter
 * *
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
     * @return void
     */
    public function injectServiceTaskContribution(ServiceTaskContribution $serviceTaskContribution) {
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    /**
     * @param ServicePerson $servicePerson
     * @return void
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param SeriesTable $seriesTable
     * @return void
     */
    public function injectSeriesTable(SeriesTable $seriesTable) {
        $this->seriesTable = $seriesTable;
    }

    /**
     * @param PersonFactory $personFactory
     * @return void
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
    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleInbox() {
        $this->setTitle(_('Inbox'), 'fa fa-envelope-open');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleDefault() {
        $this->setTitle(_('Inbox dashboard'), 'fa fa-envelope-open');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleHandout() {
        $this->setTitle(_('Rozdělení úloh opravovatelům'), 'fa fa-inbox');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList() {
        $this->setTitle(_('List of submits'), 'fa fa-cloud-download');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
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
        $control = $this->getComponent('handoutForm');
        $control->getForm()->setDefaults($values);

    }
    /* ******************* COMPONENTS ******************/

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
        $orgProvider->filterOrgs($this->getSelectedContest(), $this->getYearCalculator());
        /** @var ModelTask $task */
        foreach ($this->seriesTable->getTasks() as $task) {
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
     * @return SubmitCheckComponent
     */
    protected function createComponentCheckControl(): SubmitCheckComponent {
        return new SubmitCheckComponent($this->getContext(), $this->seriesTable);
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
        /** @var ModelTask $task */
        foreach ($this->seriesTable->getTasks() as $task) {
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

    protected function getPageStyleContainer(): PageStyleContainer {
        $container = parent::getPageStyleContainer();
        switch ($this->getAction()) {
            case 'inbox':
                $container->mainContainerClassName = str_replace('container ', 'container-fluid ', $container->mainContainerClassName).' px-3';
        }
        return $container;
    }
}
