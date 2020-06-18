<?php

namespace FKSDB\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

/**
 * Abstract functionality for basic CRUD.
 *   - check ACL
 *   - fill default form values
 *   - handling submitted data must be implemented in descendants
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class EntityPresenter extends BasePresenter {

    const COMP_EDIT_FORM = 'editComponent';
    const COMP_CREATE_FORM = 'createComponent';
    const COMP_GRID = 'grid';
    /**
     * @var int
     * @persistent
     */
    public $id;
    /**
     * @var IModel
     */
    private $model;

    /**
     * @throws BadRequestException
     */
    public function authorizedCreate() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->getModelResource(), 'create', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedEdit() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->getModel(), 'edit', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->getModelResource(), 'list', $this->getSelectedContest()));
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedDelete($id) {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->getModel(), 'delete', $this->getSelectedContest()));
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function renderEdit($id) {
        /** @var FormControl $component */
        $component = $this->getComponent(self::COMP_EDIT_FORM);
        $form = $component->getForm();
        $this->setDefaults($this->getModel(), $form);
    }

    /**
     * @throws BadRequestException
     */
    public function renderCreate() {
        /** @var FormControl $component */
        $component = $this->getComponent(self::COMP_CREATE_FORM);
        $form = $component->getForm();
        $this->setDefaults($this->getModel(), $form);
    }

    /**
     * @return AbstractModelSingle|null|IModel
     * @deprecated
     */
    final public function getModel() {
        if (!$this->model) {
            $this->model = $this->getParameter('id') ? $this->loadModel($this->getParameter('id')) : null;
        }
        return $this->model;
    }
    /**
     * @param IModel|null $model
     * @param Form $form
     * @return void
     */
    protected function setDefaults(IModel $model = null, Form $form) {
        if (!$model) {
            return;
        }
        $form->setDefaults($model->toArray());
    }

    /**
     * @param $id
     * @return AbstractModelSingle
     */
    abstract protected function loadModel($id);

    abstract protected function createComponentEditComponent(): FormControl;

    abstract protected function createComponentCreateComponent(): FormControl;

    /**
     * @return BaseGrid
     */
    abstract protected function createComponentGrid();

    abstract protected function getModelResource(): string;
}
