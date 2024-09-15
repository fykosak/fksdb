<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Components\EntityForms\SchoolFormComponent;
use FKSDB\Components\Grids\ContestantsFromSchoolGrid;
use FKSDB\Components\Grids\SchoolsGrid;
use FKSDB\Models\Authorization\Resource\ContestResource;
use FKSDB\Models\Authorization\Resource\PseudoContestResource;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;

final class SchoolsPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<SchoolModel> */
    use EntityPresenterTrait;

    private SchoolService $schoolService;

    final public function injectServiceSchool(SchoolService $schoolService): void
    {
        $this->schoolService = $schoolService;
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create school'), 'fas fa-plus');
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Schools'), 'fas fa-school');
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Detail of school %s'), $this->getEntity()->name_abbrev),
            'fas fa-university'
        );
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Edit school %s'), $this->getEntity()->name_abbrev), 'fas fa-pen');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedReport(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            new PseudoContestResource(SchoolModel::RESOURCE_ID, $this->getSelectedContest()),
            'report',
            $this->getSelectedContest()
        );
    }

    public function renderReport(): void
    {
        $tests = [];
        foreach (SchoolModel::getTests($this->getContext()) as $test) {
            $tests[$test->getId()] = $test;
        }
        $query = $this->schoolService->getTable();
        $logs = [];
        /** @var SchoolModel $model */
        foreach ($query as $model) {
            $log = DataTestFactory::runForModel($model, $tests);
            if (\count($log)) {
                $logs[] = ['model' => $model, 'logs' => $log];
            }
        }
        $this->template->tests = $tests;
        $this->template->logs = $logs;
    }

    public function titleReport(): PageTitle
    {
        return new PageTitle(null, _('Report'), 'fas fa-school');
    }

    /**
     * @param ContestResource $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    protected function getORMService(): SchoolService
    {
        return $this->schoolService;
    }

    protected function createComponentGrid(): SchoolsGrid
    {
        return new SchoolsGrid($this->getContext());
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

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    protected function createComponentContestantsFromSchoolGrid(): ContestantsFromSchoolGrid
    {
        return new ContestantsFromSchoolGrid($this->getEntity(), $this->getContext());
    }
}
