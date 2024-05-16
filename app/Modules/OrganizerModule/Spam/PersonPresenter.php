<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Spam;

use FKSDB\Components\EntityForms\Spam\PersonFormComponent;
use FKSDB\Components\Grids\Spam\PersonGrid;
use FKSDB\Models\ORM\Models\Spam\SpamPersonModel;
use FKSDB\Models\ORM\Services\Spam\SpamPersonService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Security\Resource;

final class PersonPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<SpamPersonModel> */
    use EntityPresenterTrait;

    private SpamPersonService $spamPersonService;

    public function injectSpamPersonService(SpamPersonService $spamPersonService): void
    {
        $this->spamPersonService = $spamPersonService;
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, _('Edit person %s'));
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create person'), 'fas fa-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Persons'), 'fas fa-user-group');
    }

    protected function createComponentEditForm(): PersonFormComponent
    {
        return new PersonFormComponent($this->getSelectedContestYear(), $this->getContext(), $this->getEntity());
    }

    protected function createComponentCreateForm(): PersonFormComponent
    {
        return new PersonFormComponent($this->getSelectedContestYear(), $this->getContext(), null);
    }

    protected function createComponentGrid(): PersonGrid
    {
        return new PersonGrid($this->getContext());
    }

    protected function getORMService(): SpamPersonService
    {
        return $this->spamPersonService;
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
