<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

/**
 * Trait EntityTrait
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait EntityPresenterTrait {

    /**
     * @persistent
     */
    public ?int $id = null;
    protected ?AbstractModelSingle $model;

    public function authorizedList(): void {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'list'));
    }

    public function authorizedCreate(): void {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'create'));
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     */
    public function authorizedEdit(): void {
        $this->setAuthorized($this->traitIsAuthorized($this->getEntity(), 'edit'));
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     */
    public function authorizedDelete(): void {
        $this->setAuthorized($this->traitIsAuthorized($this->getEntity(), 'delete'));
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     */
    public function authorizedDetail(): void {
        $this->setAuthorized($this->traitIsAuthorized($this->getEntity(), 'detail'));
    }
    /* ****************** TITLES ***************************** */
    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleList(): void {
        $this->setPageTitle($this->getTitleList());
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(_('List of entities'), 'fa fa-table');
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    final public function titleCreate(): void {
        $this->setPageTitle($this->getTitleCreate());
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(_('Create an entity'), 'fa fa-plus');
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(_('Edit an entity'), 'fa fa-pencil'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(_('Detail of the entity'), 'fa fa-eye'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleDelete(): void {
        $this->setPageTitle(new PageTitle(_('Delete an entity'), 'fa fa-minus'));
    }

    /**
     * @param bool $throw
     * @return AbstractModelSingle|null
     * @throws ModelNotFoundException
     */
    public function getEntity(bool $throw = true): ?AbstractModelSingle {
        $id = $this->getParameter($this->getPrimaryParameterName());
        // protection for tests ev. change URL during app is running
        if ((isset($this->model) && $id !== $this->model->getPrimary()) || !isset($this->model)) {
            $this->model = $this->loadModel($throw);
        }
        return $this->model;
    }

    /**
     * @param bool $throw
     * @return AbstractModelSingle|null
     * @throws ModelNotFoundException
     */
    private function loadModel(bool $throw = true): ?AbstractModelSingle {
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
     * @return void
     * @throws Exceptions\ModelException
     * @throws ModelNotFoundException
     */
    public function traitHandleDelete(): void {
        $success = $this->getEntity()->delete();
        if (!$success) {
            throw new Exceptions\ModelException(_('Error during deleting'));
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

    abstract protected function getORMService(): AbstractServiceSingle;

    protected function getModelResource(): string {
        return $this->getORMService()->getModelClassName()::RESOURCE_ID;
    }

    protected function getPrimaryParameterName(): string {
        return 'id';
    }

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    abstract protected function traitIsAuthorized($resource, ?string $privilege): bool;

    /**
     * @param string $name
     * @param null $default
     * @return mixed
     */
    abstract public function getParameter(string $name, $default = null);

    /**
     * @param bool $access
     * @return void
     */
    abstract public function setAuthorized(bool $access);

    /**
     * @param PageTitle $pageTitle
     * @return void
     *
     * @throws ForbiddenRequestException
     */
    abstract public function setPageTitle(PageTitle $pageTitle);
}
