<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\EntityForms\FyziklaniSubmitFormComponent;
use FKSDB\Components\Game\Submits\AllSubmitsGrid;
use FKSDB\Components\Game\Submits\Form\FormComponent;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

final class SubmitPresenter extends BasePresenter
{
    /** @phpstan-use EventEntityPresenterTrait<SubmitModel> */
    use EventEntityPresenterTrait;

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Scoring'), 'fas fa-pen');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of submit'), 'fas fa-table');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Change of scoring'), 'fas fa-pen');
    }
    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     * @throws \ReflectionException
     */
    final public function renderEdit(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->eventAuthorizator->isAllowed($resource, $privilege, $this->getEvent());
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
     * @throws ModelNotFoundException
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
