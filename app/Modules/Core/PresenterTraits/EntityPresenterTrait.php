<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\NetteORM\AbstractModel;
use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

trait EntityPresenterTrait
{

    /**
     * @persistent
     */
    public ?int $id = null;
    protected ?AbstractModel $model;

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): void
    {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'list'));
    }

    abstract public function setAuthorized(bool $access): void;

    /**
     * @param Resource|string|null $resource
     */
    abstract protected function traitIsAuthorized($resource, ?string $privilege): bool;

    protected function getModelResource(): string
    {
        return $this->getORMService()->getModelClassName()::RESOURCE_ID;
    }

    abstract protected function getORMService(): AbstractService;

    /* ****************** TITLES ***************************** */
    /**
     * @throws EventNotFoundException
     */
    public function authorizedCreate(): void
    {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'create'));
    }

    /**
     * @throws EventNotFoundException
     * @throws ModelNotFoundException
     * @throws ForbiddenRequestException
     */
    public function authorizedEdit(): void
    {
        $this->setAuthorized($this->traitIsAuthorized($this->getEntity(), 'edit'));
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getEntity(bool $throw = true): ?AbstractModel
    {
        $id = $this->getParameter($this->getPrimaryParameterName());
        // protection for tests ev. change URL during app is running
        if ((isset($this->model) && $id !== $this->model->getPrimary()) || !isset($this->model)) {
            $this->model = $this->loadModel($throw);
        }
        return $this->model;
    }

    /**
     * @param null $default
     * @return mixed
     */
    abstract public function getParameter(string $name, $default = null);

    protected function getPrimaryParameterName(): string
    {
        return 'id';
    }

    /**
     * @throws ModelNotFoundException
     */
    private function loadModel(bool $throw = true): ?AbstractModel
    {
        $id = $this->getParameter($this->getPrimaryParameterName());
        $candidate = $this->getORMService()->findByPrimary($id);
        if ($candidate) {
            return $candidate;
        } elseif ($throw) {
            throw new ModelNotFoundException('Model does not exists');
        } else {
            return null;
        }
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function authorizedDelete(): void
    {
        $this->setAuthorized($this->traitIsAuthorized($this->getEntity(), 'delete'));
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function authorizedDetail(): void
    {
        $this->setAuthorized($this->traitIsAuthorized($this->getEntity(), 'detail'));
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('List of entities'), 'fa fa-table');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('Create an entity'), 'fa fa-plus');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(_('Edit an entity'), 'fa fa-pencil');
    }

    public function titleDetail(): PageTitle
    {
        return new PageTitle(_('Detail of the entity'), 'fa fa-eye');
    }

    public function titleDelete(): PageTitle
    {
        return new PageTitle(_('Delete an entity'), 'fa fa-minus');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function traitHandleDelete(): void
    {
        $success = $this->getEntity()->delete();
        if (!$success) {
            throw new ModelException(_('Error during deleting'));
        }
    }

    /**
     * @throws NotImplementedException
     */
    abstract protected function createComponentCreateForm(): Control;

    /**
     * @throws NotImplementedException
     */
    abstract protected function createComponentEditForm(): Control;

    /**
     * @throws NotImplementedException
     */
    abstract protected function createComponentGrid(): BaseGrid;
}
