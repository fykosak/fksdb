<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Spam;

use FKSDB\Components\EntityForms\Spam\AjaxPersonFormComponent;
use FKSDB\Components\EntityForms\Spam\SpamPersonFormComponent;
use FKSDB\Components\EntityForms\Spam\SpamPersonImportComponent;
use FKSDB\Components\Grids\Spam\PersonGrid;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonMailModel;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use FKSDB\Models\ORM\Services\Spam\PersonService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;

final class PersonPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<PersonHistoryModel> */
    use EntityPresenterTrait;

    private PersonHistoryService $personHistoryService;

    public function injectSpamPersonService(PersonHistoryService $personHistoryService): void
    {
        $this->personHistoryService = $personHistoryService;
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedImport(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResourceId(PersonMailModel::RESOURCE_ID, $this->getSelectedContest()),
            'import'
        );
    }

    public function titleImport(): PageTitle
    {
        return new PageTitle(null, _('People import'), 'fas fa-download');
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws NoContestAvailable
     */
    public function authorizedEdit(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResource($this->getEntity(), $this->getSelectedContest()),
            'import'
        );
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Edit person "%s"'), $this->getEntity()->person->getFullName()),
            'fas fa-user-edit'
        );
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResourceId(PersonMailModel::RESOURCE_ID, $this->getSelectedContest()),
            'create'
        );
    }
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create person'), 'fas fa-plus');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->isAllowed(
            ContestResourceHolder::fromResourceId(PersonMailModel::RESOURCE_ID, $this->getSelectedContest()),
            'list'
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Persons'), 'fas fa-user-group');
    }


    /**
     * @throws NoContestYearAvailable
     * @throws NotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    protected function createComponentEditForm(): SpamPersonFormComponent
    {
        return new SpamPersonFormComponent($this->getSelectedContestYear(), $this->getContext(), $this->getEntity());
    }

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    protected function createComponentCreateForm(): AjaxPersonFormComponent
    {
        //return new SpamPersonFormComponent($this->getSelectedContestYear(), $this->getContext(), null);
        return new AjaxPersonFormComponent($this->getSelectedContestYear(), $this->getContext());
    }

    protected function createComponentGrid(): PersonGrid
    {
        return new PersonGrid($this->getContext());
    }

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    protected function createComponentImportForm(): SpamPersonImportComponent
    {
        return new SpamPersonImportComponent($this->getSelectedContestYear(), $this->getContext());
    }

    protected function getORMService(): PersonHistoryService
    {
        return $this->personHistoryService;
    }
}
