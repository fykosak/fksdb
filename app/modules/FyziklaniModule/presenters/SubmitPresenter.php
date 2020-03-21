<?php

namespace FyziklaniModule;

use EventModule\EventEntityTrait;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\Submit\TaskCodeInput;
use FKSDB\Components\Grids\Fyziklani\AllSubmitsGrid;
use FKSDB\Components\Grids\Fyziklani\SubmitsGrid;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\model\Fyziklani\PointsMismatchException;
use FKSDB\NotImplementedException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\RadioList;

/**
 * Class SubmitPresenter
 * @package FyziklaniModule
 * @method ModelFyziklaniSubmit getEntity()
 */
class SubmitPresenter extends BasePresenter {
    use EventEntityTrait;

    /* ***** Title methods *****/
    public function titleEntry() {
        $this->setTitle(_('Zadávání bodů'));
        $this->setIcon('fa fa-pencil-square-o');
    }

    public function titleList() {
        $this->setTitle(_('Submits'));
        $this->setIcon('fa fa-table');
    }

    public function titleEdit() {
        $this->setTitle(_('Úprava bodování'));
        $this->setIcon('fa fa-pencil');
    }

    public function titleDetail() {
        $this->setTitle(sprintf(_('Detail of the submit #%d'), $this->getEntity()->fyziklani_submit_id));
        $this->setIcon('fa fa-pencil');
    }

    /* ***** Authorized methods *****/
    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedEntry() {
        $this->setAuthorized($this->isAllowedForEventOrg('fyziklani.submit', 'default'));
    }

    /* ******** ACTION METHODS ********/

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function actionEdit(int $id) {
        $this->traitActionEdit($id);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionDetail(int $id) {
        $this->loadEntity($id);
    }

    public function renderDetail() {
        $this->template->model = $this->getEntity();
    }

    public function renderEdit() {
        $this->template->model = $this->getEntity();
    }

    /* ****** COMPONENTS **********/
    /**
     * @return TaskCodeInput
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentEntryControl(): TaskCodeInput {
        return $this->fyziklaniComponentsFactory->createTaskCodeInput($this->getEvent());
    }

    /**
     * @return SubmitsGrid
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentGrid(): SubmitsGrid {
        return new AllSubmitsGrid(
            $this->getEvent(),
            $this->getServiceFyziklaniTask(),
            $this->getServiceFyziklaniSubmit(),
            $this->getServiceFyziklaniTeam(),
            $this->getTableReflectionFactory()
        );
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     */
    public function handleCheck() {
        $log = $this->getServiceFyziklaniSubmit()->checkSubmit($this->getEntity(), $this->getEntity()->points, $this->getUser());
        $this->flashMessage($log->getMessage(), $log->getLevel());
        $this->redirect('this');
    }

    /**
     * @inheritDoc
     */
    protected function getORMService() {
        return $this->getServiceFyziklaniSubmit();
    }

    /**
     * @inheritDoc
     */
    protected function getModelResource(): string {
        return ModelFyziklaniSubmit::RESOURCE_ID;
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function isAllowed($resource, string $privilege): bool {
        return $this->isAllowedForEventOrg($resource, $privilege);
    }

    /**
     * @param AbstractModelSingle|ModelFyziklaniSubmit $model
     * @return array
     */
    protected function getFormDefaults(AbstractModelSingle $model): array {
        return [
            'team_id' => $model->e_fyziklani_team_id,
            'points' => $model->points,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getCreateForm(): FormControl {
        throw new NotImplementedException();
    }

    /**
     * @return FormControl
     * @throws AbortException
     * @throws BadRequestException
     * @throws NotSetGameParametersException
     */
    protected function getEditForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addComponent($this->createPointsField(), 'points');
        $form->addSubmit('send', _('Save'));
        return $control;
    }

    /**
     * @return RadioList
     * @throws AbortException
     * @throws BadRequestException
     * @throws NotSetGameParametersException
     */
    private function createPointsField(): RadioList {
        $field = new RadioList(_('Počet bodů'));
        $items = [];
        foreach ($this->getEvent()->getFyziklaniGameSetup()->getAvailablePoints() as $points) {
            $items[$points] = $points;
        }
        $field->setItems($items);
        $field->setRequired();
        return $field;
    }

    /**
     * @inheritDoc
     */
    protected function handleCreateFormSuccess(Form $form) {
        throw new NotImplementedException();
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    protected function handleEditFormSuccess(Form $form) {
        $values = $form->getValues();
        try {
            $msg = $this->getORMService()->changePoints($this->getEntity(), $values->points, $this->getPresenter()->getUser());
            $this->getPresenter()->flashMessage($msg->getMessage(), $msg->getLevel());
            $this->redirect('this');
        } catch (ClosedSubmittingException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
    }
}
