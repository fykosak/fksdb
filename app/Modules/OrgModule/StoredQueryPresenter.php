<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\StoredQuery\StoredQueryTagCloudComponent;
use FKSDB\Components\EntityForms\StoredQueryFormComponent;
use FKSDB\Components\Grids\StoredQuery\StoredQueriesGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use FKSDB\Models\ORM\Services\StoredQuery\QueryService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use Fykosak\Utils\UI\PageTitle;
use Nette\Security\Resource;

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
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Edit query %s'), $this->getEntity()->name), 'fas fa-pen');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create query'), 'fas fa-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Exports'), 'fas fa-file-csv');
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
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
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function startup(): void
    {
        switch ($this->getAction()) {
            case 'execute':
                $this->redirect(':Org:Export:execute', $this->getParameters());
        }
        parent::startup();
    }

    protected function createComponentCreateForm(): StoredQueryFormComponent
    {
        return new StoredQueryFormComponent($this->getContext(), null);
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
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

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
