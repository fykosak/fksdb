<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Inbox\CorrectedControl;
use FKSDB\Components\Controls\Inbox\SubmitsPreviewControl;
use FKSDB\Components\Controls\Inbox\SubmitCheckComponent;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Controls\Inbox\InboxControl;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\CoreModule\SeriesPresenter\{ISeriesPresenter, SeriesPresenterTrait};
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
class InboxPresenter extends BasePresenter implements ISeriesPresenter {

    use SeriesPresenterTrait;

    public const TASK_PREFIX = 'task';

    private ServiceTaskContribution $serviceTaskContribution;

    private ServicePerson $servicePerson;

    private SeriesTable $seriesTable;

    private PersonFactory $personFactory;

    public function injectServiceTaskContribution(ServiceTaskContribution $serviceTaskContribution): void {
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    public function injectServicePerson(ServicePerson $servicePerson): void {
        $this->servicePerson = $servicePerson;
    }

    public function injectSeriesTable(SeriesTable $seriesTable): void {
        $this->seriesTable = $seriesTable;
    }

    public function injectPersonFactory(PersonFactory $personFactory): void {
        $this->personFactory = $personFactory;
    }
    /* ***************** AUTH ***********************/

    /**
     * @return void
     */
    public function authorizedDefault(): void {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest()));
    }

    /**
     * @return void
     */
    public function authorizedInbox(): void {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest()));
    }

    /**
     * @return void
     */
    public function authorizedList(): void {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'list', $this->getSelectedContest()));
    }

    /**
     * @return void
     */
    public function authorizedHandout(): void {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('task', 'edit', $this->getSelectedContest()));
    }

    /**
     * @return void
     */
    public function authorizedCorrected(): void {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'corrected', $this->getSelectedContest()));
    }

    /* ***************** TITLES ***********************/
    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleInbox(): void {
        $this->setTitle(_('Inbox'), 'fa fa-envelope-open');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleDefault(): void {
        $this->setTitle(_('Inbox dashboard'), 'fa fa-envelope-open');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleHandout(): void {
        $this->setTitle(_('Rozdělení úloh opravovatelům'), 'fa fa-inbox');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList(): void {
        $this->setTitle(_('List of submits'), 'fa fa-cloud-download');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleCorrected(): void {
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
        $this->seriesTraitStartup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    public function actionHandout(): void {
        // This workaround fixes inproper caching of referenced tables.
        // $connection = $this->servicePerson->getConnection();
        // $connection->getCache()->clean(array(Cache::ALL => true));
        // $connection->getDatabaseReflection()->setConnection($connection);
    }

    /* ******************** RENDER ****************/

    /**
     * @throws BadRequestException
     */
    public function renderHandout(): void {
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
    protected function createComponentHandoutForm(): FormControl {
        $formControl = new FormControl($this->getContext());
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

    public function createComponentCorrectedFormControl(): CorrectedControl {
        return new CorrectedControl($this->getContext(), $this->seriesTable);
    }

    protected function createComponentCheckControl(): SubmitCheckComponent {
        return new SubmitCheckComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentSubmitsTableControl(): SubmitsPreviewControl {
        return new SubmitsPreviewControl($this->getContext(), $this->seriesTable);
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    public function handoutFormSuccess(Form $form): void {
        $values = $form->getValues();

        $service = $this->serviceTaskContribution;
        $connection = $service->getConnection();

        $connection->beginTransaction();
        /** @var ModelTask $task */
        foreach ($this->seriesTable->getTasks() as $task) {
            $service->getTable()->where([
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

        $this->flashMessage(_('Přiřazení opravovatelů uloženo.'), self::FLASH_SUCCESS);
        $this->redirect('this');
    }

    protected function getPageStyleContainer(): PageStyleContainer {
        $container = parent::getPageStyleContainer();
        switch ($this->getAction()) {
            case 'inbox':
                $container->mainContainerClassName = str_replace('container ', 'container-fluid ', $container->mainContainerClassName) . ' px-3';
        }
        return $container;
    }

    /**
     * @param string $title
     * @param string $icon
     * @param string $subTitle
     * @throws BadRequestException
     */
    protected function setTitle(string $title, string $icon = '', string $subTitle = ''): void {
        parent::setTitle($title, $icon, $subTitle . ' ' . sprintf(_('%d. series'), $this->getSelectedSeries()));
    }
}
