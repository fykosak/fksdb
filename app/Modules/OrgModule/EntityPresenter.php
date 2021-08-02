<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\AbstractModel;
use Nette\Application\UI\Form;

/**
 * Abstract functionality for basic CRUD.
 *   - check ACL
 *   - fill default form values
 *   - handling submitted data must be implemented in descendants
 */
abstract class EntityPresenter extends BasePresenter
{

    public const COMP_EDIT_FORM = 'editComponent';
    public const COMP_CREATE_FORM = 'createComponent';
    /**
     * @persistent
     */
    public ?int $id = null;
    private ?AbstractModel $model;

    public function authorizedCreate(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed($this->getModelResource(), 'create', $this->getSelectedContest()));
    }

    public function authorizedEdit(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed($this->getModel(), 'edit', $this->getSelectedContest()));
    }

    public function authorizedList(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed($this->getModelResource(), 'list', $this->getSelectedContest()));
    }

    public function authorizedDelete(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed($this->getModel(), 'delete', $this->getSelectedContest()));
    }

    /**
     * @throws BadTypeException
     */
    final public function renderEdit(): void
    {
        /** @var FormControl $component */
        $component = $this->getComponent(self::COMP_EDIT_FORM);
        $form = $component->getForm();
        $this->setDefaults($this->getModel(), $form);
    }

    /**
     * @throws BadTypeException
     */
    final public function renderCreate(): void
    {
        /** @var FormControl $component */
        $component = $this->getComponent(self::COMP_CREATE_FORM);
        $form = $component->getForm();
        $this->setDefaults($this->getModel(), $form);
    }

    /**
     * @return AbstractModel|null
     * @deprecated
     */
    final public function getModel(): ?AbstractModel
    {
        if (!isset($this->model)) {
            $this->model = $this->getParameter('id') ? $this->loadModel($this->getParameter('id')) : null;
        }
        return $this->model;
    }

    protected function setDefaults(?AbstractModel $model, Form $form): void
    {
        if (!$model) {
            return;
        }
        $form->setDefaults($model->toArray());
    }

    abstract protected function loadModel(int $id): ?AbstractModel;

    abstract protected function createComponentEditComponent(): FormControl;

    abstract protected function createComponentCreateComponent(): FormControl;

    abstract protected function createComponentGrid(): BaseGrid;

    abstract protected function getModelResource(): string;
}
