<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\Service;
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
     * @throws NoContestYearAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->traitIsAuthorized($this->getModelResource(), 'create');
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

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    public function authorizedList(): bool
    {
        return $this->traitIsAuthorized($this->getModelResource(), 'list');
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    public function authorizedDefault(): bool
    {
        return $this->traitIsAuthorized($this->getModelResource(), 'list');
    }

    /**
     * @throws GoneException
     * @phpstan-return string
     */
    protected function getModelResource()
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
