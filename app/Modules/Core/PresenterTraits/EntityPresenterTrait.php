<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

trait EntityPresenterTrait
{
    /** @persistent */
    public ?int $id = null;

    /**
     * @throws EventNotFoundException
     * @throws GoneException
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

    /**
     * @throws GoneException
     */
    protected function getModelResource(): string
    {
        return $this->getORMService()->getModelClassName()::RESOURCE_ID;
    }

    abstract protected function getORMService(): Service;

    /* ****************** TITLES ***************************** */
    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedCreate(): void
    {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'create'));
    }

    /**
     * @throws EventNotFoundException
     * @throws ModelNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function authorizedEdit(): void
    {
        $this->setAuthorized($this->traitIsAuthorized($this->getEntity(), 'edit'));
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function getEntity(bool $throw = true): ?Model
    {
        static $model;
        // protection for tests ev . change URL during app is running
        if (!isset($model) || $this->id !== $model->getPrimary()) {
            $model = $this->loadModel($throw);
        }
        return $model;
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    private function loadModel(bool $throw = true): ?Model
    {
        $candidate = $this->getORMService()->findByPrimary($this->id);
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
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function authorizedDelete(): void
    {
        $this->setAuthorized($this->traitIsAuthorized($this->getEntity(), 'delete'));
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function authorizedDetail(): void
    {
        $this->setAuthorized($this->traitIsAuthorized($this->getEntity(), 'detail'));
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of entities'), 'fa fa-table');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create an entity'), 'fa fa-plus');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit an entity'), 'fa fa-pencil');
    }

    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, _('Detail of the entity'), 'fa fa-eye');
    }

    public function titleDelete(): PageTitle
    {
        return new PageTitle(null, _('Delete an entity'), 'fa fa-minus');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws \ReflectionException
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
