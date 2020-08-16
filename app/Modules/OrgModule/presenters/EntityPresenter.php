<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use Nette\Application\ForbiddenRequestException;
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

    public const COMP_EDIT_FORM = 'editComponent';
    public const COMP_CREATE_FORM = 'createComponent';

    /**
     * @var int
     * @persistent
     */
    public $id;
    /** @var IModel */
    private $model;

    /**
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    public function authorizedCreate(): void {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->getModelResource(), 'create', $this->getSelectedContest()));
    }

    /**
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    public function authorizedEdit(): void {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->getModel(), 'edit', $this->getSelectedContest()));
    }

    /**
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    public function authorizedList(): void {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->getModelResource(), 'list', $this->getSelectedContest()));
    }

    /**
     * @param int $id
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    public function authorizedDelete($id): void {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->getModel(), 'delete', $this->getSelectedContest()));
    }

    /**
     * @param int $id
     * @throws BadTypeException
     */
    public function renderEdit($id): void {
        /** @var FormControl $component */
        $component = $this->getComponent(self::COMP_EDIT_FORM);
        $form = $component->getForm();
        $this->setDefaults($this->getModel(), $form);
    }

    /**
     * @throws BadTypeException
     */
    public function renderCreate(): void {
        /** @var FormControl $component */
        $component = $this->getComponent(self::COMP_CREATE_FORM);
        $form = $component->getForm();
        $this->setDefaults($this->getModel(), $form);
    }

    /**
     * @return AbstractModelSingle|null|IModel
     * @deprecated
     */
    final public function getModel(): ?IModel {
        if (!$this->model) {
            $this->model = $this->getParameter('id') ? $this->loadModel($this->getParameter('id')) : null;
        }
        return $this->model;
    }

    protected function setDefaults(?IModel $model, Form $form): void {
        if (!$model) {
            return;
        }
        $form->setDefaults($model->toArray());
    }

    /**
     * @param int $id
     * @return AbstractModelSingle
     */
    abstract protected function loadModel($id): ?IModel;

    abstract protected function createComponentEditComponent(): FormControl;

    abstract protected function createComponentCreateComponent(): FormControl;

    abstract protected function createComponentGrid(): BaseGrid;

    abstract protected function getModelResource(): string;
}
