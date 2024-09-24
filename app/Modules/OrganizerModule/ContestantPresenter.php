<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Contestants\ContestantForm;
use FKSDB\Components\Contestants\SubmitsGrid;
use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\DataTest\TestsList;
use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\Models\Authorization\Resource\PseudoContestYearResource;
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
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function authorizedEdit(): bool
    {
        return $this->authorizator->isAllowedContestYear(
            $this->getEntity(),
            'edit',
            $this->getSelectedContestYear()
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
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function authorizedDetail(): bool
    {
        return $this->authorizator->isAllowedContestYear(
            $this->getEntity(),
            'detail',
            $this->getSelectedContestYear()
        );
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Detail of the contestant %s'), $this->getEntity()->person->getFullName()),
            'fas fa-user'
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

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedContestYear(
            new PseudoContestYearResource(ContestantModel::RESOURCE_ID, $this->getSelectedContestYear()),
            'create',
            $this->getSelectedContestYear()
        );
    }
    public function titleCreate(): PageTitle
    {
        return new PageTitle('contestant-create', _('Create contestant'), 'fas fa-user-plus');
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    public function authorizedList(): bool
    {
        return $this->authorizator->isAllowedContestYear(
            new PseudoContestYearResource(ContestantModel::RESOURCE_ID, $this->getSelectedContestYear()),
            'list',
            $this->getSelectedContestYear()
        );
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

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function getModelResource(): PseudoContestYearResource
    {
        return new PseudoContestYearResource(ContestantModel::RESOURCE_ID, $this->getSelectedContestYear());
    }
}
