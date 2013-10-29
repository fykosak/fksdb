<?php

namespace OrgModule;

use AbstractModelSingle;
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
     * @var AbstractModelSingle
     */
    private $model = false;

    /**
     * Name of the resource that is tested in operations.
     * @var string
     */
    protected $modelResourceId;

    public function authorizedCreate() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->modelResourceId, 'create', $this->getSelectedContest()));
    }

    public function authorizedEdit($id) {
        $model = $this->getModel();
        if (!$model) {
            throw new BadRequestException('Neexistující model.', 404);
        }
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($model, 'edit', $this->getSelectedContest()));
    }

    public function authorizedList() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->modelResourceId, 'list', $this->getSelectedContest()));
    }

    public function authorizedDelete($id) {
        $model = $this->getModel();
        if (!$model) {
            throw new BadRequestException('Neexistující model.', 404);
        }
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($model, 'delete', $this->getSelectedContest()));
    }

    public function renderEdit($id) {
        $this->setDefaults($this->getModel(), $this->getComponent(self::COMP_EDIT_FORM));
    }

    protected function getModel() {
        if ($this->model === false) {
            $this->model = $this->createModel($this->getParam('id'));
        }

        return $this->model;
    }

    protected function setDefaults(AbstractModelSingle $model, Form $form) {
        $form->setDefaults($model->toArray());
    }

    /**
     * @return AbstracModelSingle
     */
    abstract protected function createModel($id);

    abstract protected function createComponentEditComponent($name);

    abstract protected function createComponentCreateComponent($name);

    abstract protected function createComponentGrid($name);
}
