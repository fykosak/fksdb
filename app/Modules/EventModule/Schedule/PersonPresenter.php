<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\Controls\Transition\TransitionButtonsComponent;
use FKSDB\Components\Schedule\Forms\PersonScheduleForm;
use FKSDB\Components\Schedule\PersonScheduleList;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Nette\Security\Resource;

final class PersonPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<PersonScheduleModel> */
    use EntityPresenterTrait;

    private PersonScheduleService $service;

    public function inject(PersonScheduleService $service): void
    {
        $this->service = $service;
    }

    public function actionDelete(): void
    {
        try {
            $this->traitHandleDelete();
        } catch (\Throwable $exception) {
            $this->flashMessage(_('Error') . ': ' . $exception->getMessage(), Message::LVL_ERROR);
            $this->redirect('list');
        }
        $this->flashMessage(_('Entity has been deleted'), Message::LVL_WARNING);
        $this->redirect('list');
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
     */
    public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleDetail(): PageTitle
    {
        $model = $this->getEntity();
        return new PageTitle(
            null,
            sprintf(
                _('%s@%s: %s'),
                $model->schedule_item->name->get($this->translator->lang),
                $model->schedule_item->schedule_group->name->get($this->translator->lang),
                $model->person->getFullName()
            ),
            'fas fa-list'
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Schedule'), 'fas fa-list');
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->eventAuthorizator->isAllowed(PersonScheduleModel::RESOURCE_ID, $privilege, $this->getEvent());
    }

    protected function getORMService(): PersonScheduleService
    {
        return $this->service;
    }

    /**
     * @phpstan-return TransitionButtonsComponent<PersonScheduleModel>
     * @throws NotFoundException
     * @throws CannotAccessModelException
     * @throws GoneException
     */
    protected function createComponentButtonTransition(): TransitionButtonsComponent
    {
        return new TransitionButtonsComponent(
            $this->getContext(),
            $this->eventDispatchFactory->getPersonScheduleMachine(), // @phpstan-ignore-line
            $this->getEntity()
        );
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentGrid(): PersonScheduleList
    {
        return new PersonScheduleList($this->getContext(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentCreateForm(): PersonScheduleForm
    {
        return new PersonScheduleForm($this->getEvent(), $this->getContext(), null);
    }

    /**
     * @throws EventNotFoundException
     * @throws GoneException
     * @throws NotFoundException
     */
    protected function createComponentEditForm(): PersonScheduleForm
    {
        return new PersonScheduleForm($this->getEvent(), $this->getContext(), $this->getEntity());
    }
}
