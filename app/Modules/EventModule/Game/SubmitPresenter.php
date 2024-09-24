<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\EntityForms\FyziklaniSubmitFormComponent;
use FKSDB\Components\Game\Submits\AllSubmitsGrid;
use FKSDB\Components\Game\Submits\Form\FormComponent;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

final class SubmitPresenter extends BasePresenter
{
    /** @phpstan-use EventEntityPresenterTrait<SubmitModel> */
    use EventEntityPresenterTrait;

    /**
     * @throws EventNotFoundException
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource(SubmitModel::RESOURCE_ID, $this->getEvent()),
            'create',
            $this->getEvent()
        );
    }
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Scoring'), 'fas fa-pen');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource(SubmitModel::RESOURCE_ID, $this->getEvent()),
            'list',
            $this->getEvent()
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of submit'), 'fas fa-table');
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     * @throws ForbiddenRequestException
     * @throws EventNotFoundException
     */
    public function authorizedEdit(): bool
    {
        return $this->authorizator->isAllowedEvent(
            $this->getEntity(),
            'create',
            $this->getEvent()
        );
    }
    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Change of scoring'), 'fas fa-pen');
    }
    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    final public function renderEdit(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): AllSubmitsGrid
    {
        return new AllSubmitsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): FormComponent
    {
        return new FormComponent($this->getContext(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): FyziklaniSubmitFormComponent
    {
        return new FyziklaniSubmitFormComponent($this->getContext(), $this->getEntity());
    }

    protected function getORMService(): SubmitService
    {
        return $this->submitService;
    }
}
