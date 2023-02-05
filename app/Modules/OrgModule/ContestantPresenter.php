<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\EntityForms\ContestantFormComponent;
use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Services\ContestantService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

/**
 * @method ContestantModel getEntity(bool $throw = true)
 */
class ContestantPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private ContestantService $contestantService;

    final public function injectServiceContestant(ContestantService $contestantService): void
    {
        $this->contestantService = $contestantService;
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            'contestant-edit',
            sprintf(_('Edit the contestant %s'), $this->getEntity()->person->getFullName()),
            'fa fa-user-edit'
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle('contestant-create', _('Create contestant'), 'fa fa-user-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle('contestant-list', _('Contestants'), 'fa fa-user-graduate');
    }

    protected function createComponentGrid(): ContestantsGrid
    {
        return new ContestantsGrid($this->getContext(), $this->getSelectedContestYear());
    }

    protected function getORMService(): ContestantService
    {
        return $this->contestantService;
    }

    protected function getModelResource(): string
    {
        return ContestantModel::RESOURCE_ID;
    }

    /**
     * @param Resource|string $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    protected function createComponentCreateForm(): Control
    {
        return new ContestantFormComponent($this->getSelectedContestYear(), $this->getContext(), null);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): Control
    {
        return new ContestantFormComponent($this->getSelectedContestYear(), $this->getContext(), $this->getEntity());
    }
}
