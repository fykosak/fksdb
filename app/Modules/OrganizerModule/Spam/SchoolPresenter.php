<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Spam;

use FKSDB\Components\EntityForms\Spam\SchoolFormComponent;
use FKSDB\Components\Grids\Spam\SchoolGrid;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Spam\SpamSchoolModel;
use FKSDB\Models\ORM\Services\Spam\SpamSchoolService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

final class SchoolPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<SpamSchoolModel> */
    use EntityPresenterTrait;

    private SpamSchoolService $spamSchoolService;

    public function injectSpamSchoolService(SpamSchoolService $spamSchoolService): void
    {
        $this->spamSchoolService = $spamSchoolService;
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Edit school %s'), $this->getEntity()->spam_school_label), 'fas fa-pen');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create school'), 'fas fa-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Schools'), 'fas fa-school');
    }


    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    protected function createComponentEditForm(): SchoolFormComponent
    {
        return new SchoolFormComponent($this->getContext(), $this->getEntity());
    }

    protected function createComponentCreateForm(): SchoolFormComponent
    {
        return new SchoolFormComponent($this->getContext(), null);
    }

    protected function createComponentGrid(): Control
    {
        return new SchoolGrid($this->getContext());
    }

    protected function getORMService(): SpamSchoolService
    {
        return $this->spamSchoolService;
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
