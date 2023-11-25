<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\EntityForms\ContestantFormComponent;
use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Services\ContestantService;
use FKSDB\Models\Results\ResultsModelFactory;
use FKSDB\Modules\Core\PresenterTraits\ContestYearEntityTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidArgumentException;
use Nette\Security\Resource;

final class ContestantPresenter extends BasePresenter
{
    /** @phpstan-use ContestYearEntityTrait<ContestantModel> */
    use ContestYearEntityTrait;

    private ContestantService $contestantService;

    final public function injectServiceContestant(ContestantService $contestantService): void
    {
        $this->contestantService = $contestantService;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            'contestant-edit',
            sprintf(_('Edit the contestant %s'), $this->getEntity()->person->getFullName()),
            'fas fa-user-edit'
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle('contestant-create', _('Create contestant'), 'fas fa-user-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle('contestant-list', _('Contestants'), 'fas fa-user-graduate');
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentGrid(): ContestantsGrid
    {
        return new ContestantsGrid($this->getContext(), $this->getSelectedContestYear());
    }

    protected function getORMService(): ContestantService
    {
        return $this->contestantService;
    }

    /**
     * @throws BadRequestException
     */
    public function handleRecalculate(): void
    {
        $contestants = $this->getSelectedContestYear()->getContestants();
        $strategy = ResultsModelFactory::findEvaluationStrategy(
            $this->getContext(),
            $this->getSelectedContestYear()
        );
        /** @var ContestantModel $contestant */
        foreach ($contestants as $contestant) {
            try {
                $strategy->updateCategory($contestant);
            } catch (InvalidArgumentException $exception) {
                $this->flashMessage($exception->getMessage());
            }
        }
    }

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    protected function createComponentCreateForm(): ContestantFormComponent
    {
        return new ContestantFormComponent($this->getSelectedContestYear(), $this->getContext(), null);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): ContestantFormComponent
    {
        return new ContestantFormComponent($this->getSelectedContestYear(), $this->getContext(), $this->getEntity());
    }
}
