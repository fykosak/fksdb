<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Spam;

use FKSDB\Components\EntityForms\Spam\AjaxPersonFormComponent;
use FKSDB\Components\EntityForms\Spam\SpamPersonFormComponent;
use FKSDB\Components\EntityForms\Spam\SpamPersonImportComponent;
use FKSDB\Components\Grids\Spam\PersonGrid;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use FKSDB\Models\ORM\Services\Spam\SpamPersonService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Security\Resource;

final class PersonPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<PersonHistoryModel> */
    use EntityPresenterTrait;

    private PersonHistoryService $personHistoryService;

    public function injectSpamPersonService(PersonHistoryService $personHistoryService): void
    {
        $this->personHistoryService = $personHistoryService;
    }

    public function authorizedImport(): bool
    {
        return $this->traitIsAuthorized($this->getModelResource(), 'import');
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Edit person "%s"'), $this->getEntity()->person->getFullName()),
            'fas fa-user-edit'
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create person'), 'fas fa-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Persons'), 'fas fa-user-group');
    }

    public function titleImport(): PageTitle
    {
        return new PageTitle(null, _('Import people'), 'fas fa-download');
    }

    protected function createComponentEditForm(): SpamPersonFormComponent
    {
        return new SpamPersonFormComponent($this->getSelectedContestYear(), $this->getContext(), $this->getEntity());
    }

    protected function createComponentCreateForm(): AjaxPersonFormComponent
    {
        //return new SpamPersonFormComponent($this->getSelectedContestYear(), $this->getContext(), null);
        return new AjaxPersonFormComponent($this->getSelectedContestYear(), $this->getContext());
    }

    protected function createComponentGrid(): PersonGrid
    {
        return new PersonGrid($this->getContext());
    }

    protected function createComponentImportForm(): SpamPersonImportComponent
    {
        return new SpamPersonImportComponent($this->getSelectedContestYear(), $this->getContext());
    }

    protected function getORMService(): PersonHistoryService
    {
        return $this->personHistoryService;
    }

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }
}
