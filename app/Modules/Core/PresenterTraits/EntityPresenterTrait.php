<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\Service;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

/**
 * @phpstan-template TModel of (Model&Resource)
 */
trait EntityPresenterTrait
{
    /**
     * @persistent
     */
    public ?int $id = null;

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->traitIsAuthorized($this->getModelResource(), 'create');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create an entity'), 'fas fa-plus');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     */
    public function authorizedDelete(): bool
    {
        return $this->traitIsAuthorized($this->getEntity(), 'delete');
    }

    public function titleDelete(): PageTitle
    {
        return new PageTitle(null, _('Delete an entity'), 'fas fa-minus');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     */
    public function authorizedDetail(): bool
    {
        return $this->traitIsAuthorized($this->getEntity(), 'detail');
    }

    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, _('Detail of the entity'), 'fas fa-eye');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     */
    public function authorizedEdit(): bool
    {
        return $this->traitIsAuthorized($this->getEntity(), 'edit');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit an entity'), 'fas fa-pencil');
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->traitIsAuthorized($this->getModelResource(), 'list');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of entities'), 'fas fa-table');
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    public function authorizedDefault(): bool
    {
        return $this->traitIsAuthorized($this->getModelResource(), 'list');
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('List of entities'), 'fas fa-table');
    }

    /**
     * @throws GoneException
     */
    protected function getModelResource(): string
    {
        return $this->getORMService()->getModelClassName()::RESOURCE_ID;
    }

    /**
     * @phpstan-return TModel
     * @throws GoneException
     * @throws NotFoundException
     */
    public function getEntity(): Model
    {
        static $model;
        // protection for tests ev . change URL during app is running
        if (!isset($model) || $this->id !== $model->getPrimary()) {
            $model = $this->loadModel();
        }
        return $model;
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
     * @phpstan-return TModel
     */
    private function loadModel(): Model
    {
        /** @phpstan-var TModel|null $candidate */
        $candidate = $this->getORMService()->findByPrimary($this->id);
        if ($candidate) {
            return $candidate;
        } else {
            throw new NotFoundException(_('Model does not exist.'));
        }
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     */
    public function traitHandleDelete(): void
    {
        $this->getORMService()->disposeModel($this->getEntity());
    }

    /**
     * @phpstan-return Service<TModel>
     */
    abstract protected function getORMService(): Service;

    /**
     * @param Resource|string|null $resource
     */
    abstract protected function traitIsAuthorized($resource, ?string $privilege): bool;

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
