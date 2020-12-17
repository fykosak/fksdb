<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\IModel;
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
     * @persistent
     */
    public ?int $id = null;
    private ?IModel $model;

    public function authorizedCreate(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed($this->getModelResource(), 'create', $this->getSelectedContest()));
    }

    public function authorizedEdit(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed($this->getModel(), 'edit', $this->getSelectedContest()));
    }

    public function authorizedList(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed($this->getModelResource(), 'list', $this->getSelectedContest()));
    }

    /**
     * @param int $id
     */
    public function authorizedDelete($id): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed($this->getModel(), 'delete', $this->getSelectedContest()));
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
        if (!isset($this->model)) {
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
