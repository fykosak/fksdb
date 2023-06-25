<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Service;
use Fykosak\Utils\UI\PageTitle;
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
    public function authorizedList(): bool
    {
        return $this->traitIsAuthorized($this->getModelResource(), 'list');
    }

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

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     */
    public function authorizedCreate(): bool
    {
        return $this->traitIsAuthorized($this->getModelResource(), 'create');
    }

    /**
     * @throws EventNotFoundException
     * @throws ModelNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function authorizedEdit(): bool
    {
        return $this->traitIsAuthorized($this->getEntity(), 'edit');
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
            throw new ModelNotFoundException(_('Model does not exists'));
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
    public function authorizedDelete(): bool
    {
        return $this->traitIsAuthorized($this->getEntity(), 'delete');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     */
    public function authorizedDetail(): bool
    {
        return $this->traitIsAuthorized($this->getEntity(), 'detail');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of entities'), 'fas fa-table');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create an entity'), 'fas fa-plus');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit an entity'), 'fas fa-pencil');
    }

    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, _('Detail of the entity'), 'fas fa-eye');
    }

    public function titleDelete(): PageTitle
    {
        return new PageTitle(null, _('Delete an entity'), 'fas fa-minus');
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
    abstract protected function createComponentGrid(): Control;
}
