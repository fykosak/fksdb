<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Contestants\ContestantForm;
use FKSDB\Components\Contestants\SubmitsGrid;
use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\TestsList;
use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
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
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            'contestant-edit',
            sprintf(_('Edit the contestant %s'), $this->getEntity()->person->getFullName()),
            'fas fa-user-edit'
        );
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle('contestant-create', _('Create contestant'), 'fas fa-user-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle('contestant-list', _('Contestants'), 'fas fa-user-graduate');
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
     * @throws NoContestYearAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestYearAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContestYear());
    }

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    protected function createComponentCreateForm(): ContestantForm
    {
        return new ContestantForm($this->getSelectedContestYear(), $this->getContext(), null);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    protected function createComponentEditForm(): ContestantForm
    {
        return new ContestantForm($this->getSelectedContestYear(), $this->getContext(), $this->getEntity());
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentGrid(): ContestantsGrid
    {
        return new ContestantsGrid($this->getContext(), $this->getSelectedContestYear());
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    protected function createComponentSubmits(): SubmitsGrid
    {
        return new SubmitsGrid($this->getContext(), $this->getEntity());
    }

    /**
     * @phpstan-return TestsList<ContestantModel>
     */
    protected function createComponentTests(): TestsList
    {
        return new TestsList($this->getContext(), DataTestFactory::getContestantTests($this->getContext()), true);
    }
}
