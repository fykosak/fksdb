<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Controls\StoredQuery\StoredQueryTagCloudComponent;
use FKSDB\Components\EntityForms\StoredQueryFormComponent;
use FKSDB\Components\Grids\StoredQuery\StoredQueriesGrid;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use FKSDB\Models\ORM\Services\StoredQuery\QueryService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use Fykosak\Utils\UI\PageTitle;

final class StoredQueryPresenter extends BasePresenter
{
    use SeriesPresenterTrait;
    /** @phpstan-use EntityPresenterTrait<QueryModel> */
    use EntityPresenterTrait;

    private QueryService $storedQueryService;

    final public function injectServiceStoredQuery(QueryService $storedQueryService): void
    {
        $this->storedQueryService = $storedQueryService;
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    public function authorizedEdit(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResource($this->getEntity(), $this->getSelectedContest()),
            'edit',
            $this->getSelectedContest()
        );
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Edit query %s'), $this->getEntity()->name), 'fas fa-pen');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(QueryModel::RESOURCE_ID, $this->getSelectedContest()),
            'create',
            $this->getSelectedContest()
        );
    }
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create query'), 'fas fa-plus');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(QueryModel::RESOURCE_ID, $this->getSelectedContest()),
            'list',
            $this->getSelectedContest()
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Exports'), 'fas fa-file-csv');
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws NoContestAvailable
     */
    public function authorizedDetail(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResource($this->getEntity(), $this->getSelectedContest()),
            'detail',
            $this->getSelectedContest()
        );
    }
    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleDetail(): PageTitle
    {
        $title = sprintf(_('Detail of the query "%s"'), $this->getEntity()->name);
        $qid = $this->getEntity()->qid;
        if ($qid) {
            $title .= " ($qid)";
        }

        return new PageTitle(null, $title, 'fas fa-file-csv');
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function createComponentCreateForm(): StoredQueryFormComponent
    {
        return new StoredQueryFormComponent($this->getContext(), null);
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    protected function createComponentEditForm(): StoredQueryFormComponent
    {
        return new StoredQueryFormComponent($this->getContext(), $this->getEntity());
    }

    protected function createComponentGrid(): StoredQueriesGrid
    {
        /** @var StoredQueryTagCloudComponent $cloud */
        $cloud = $this->getComponent('tagCloud');
        return new StoredQueriesGrid($this->getContext(), $cloud->getActiveTagIds());
    }

    protected function createComponentTagCloud(): StoredQueryTagCloudComponent
    {
        return new StoredQueryTagCloudComponent($this->getContext());
    }

    protected function getORMService(): QueryService
    {
        return $this->storedQueryService;
    }
}
