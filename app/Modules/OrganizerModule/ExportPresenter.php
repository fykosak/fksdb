<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Controls\StoredQuery\ResultsComponent;
use FKSDB\Components\Controls\StoredQuery\StoredQueryTagCloudComponent;
use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use FKSDB\Models\ORM\Services\StoredQuery\QueryService;
use FKSDB\Models\StoredQuery\StoredQuery;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;
use Nette\Utils\Strings;

final class ExportPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<QueryModel> */
    use EntityPresenterTrait;

    private QueryService $queryService;
    private StoredQueryFactory $storedQueryFactory;
    private StoredQuery $storedQuery;

    final public function injectServiceStoredQuery(
        QueryService $queryService,
        StoredQueryFactory $storedQueryFactory
    ): void {
        $this->queryService = $queryService;
        $this->storedQueryFactory = $storedQueryFactory;
    }

    /**
     * @throws BadRequestException
     */
    public function titleExecute(): PageTitle
    {
        return new PageTitle(null, $this->getStoredQuery()->getName(), 'fas fa-play-circle');
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedExecute(): bool
    {
        return $this->contestAuthorizator->isAllowed($this->getStoredQuery(), 'execute', $this->getSelectedContest());
    }

    /**
     * @throws BadRequestException
     */
    public function getStoredQuery(): StoredQuery
    {
        if (!isset($this->storedQuery)) {
            $model = $this->getQueryByQId();
            if (!$model) {
                $model = $this->getEntity();
            }
            $this->storedQuery = $this->storedQueryFactory->createQuery($this, $model);
        }
        return $this->storedQuery;
    }

    public function getQueryByQId(): ?QueryModel
    {
        $qid = $this->getParameter('qid');
        if ($qid) {
            return $this->queryService->findByQid($qid);
        }
        return null;
    }

    /**
     * @throws BadRequestException
     */
    public function actionExecute(): void
    {
        if ($this->getParameter('qid')) {
            $parameters = [];
            foreach ($this->getParameters() as $key => $value) {
                if (Strings::startsWith($key, ResultsComponent::PARAMETER_URL_PREFIX)) {
                    $parameters[substr($key, strlen(ResultsComponent::PARAMETER_URL_PREFIX))] = $value;
                }
            }
            $this->getStoredQuery()->setParameters($parameters);
            if ($this->getParameter('format')) {
                /** @var ResultsComponent $resultsComponent */
                $resultsComponent = $this->getComponent('resultsComponent');
                $resultsComponent->handleFormat($this->getParameter('format'));
            }
        }
    }

    /**
     * @throws BadRequestException
     */
    final public function renderExecute(): void
    {
        $this->template->model = $this->getStoredQuery()->queryPattern;
    }

    protected function getHttpRealm(): ?string
    {
        return 'FKSDB-export';
    }

    /**
     * @throws BadRequestException
     */
    protected function createComponentResultsComponent(): ResultsComponent
    {
        $control = new ResultsComponent($this->getContext());
        $control->storedQuery = $this->getStoredQuery();
        return $control;
    }

    protected function createComponentTagCloud(): StoredQueryTagCloudComponent
    {
        return new StoredQueryTagCloudComponent($this->getContext());
    }

    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function getORMService(): QueryService
    {
        return $this->queryService;
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return false;
    }

    /**
     * @return never
     * @throws NotImplementedException
     */
    protected function createComponentGrid(): BaseGrid
    {
        throw new NotImplementedException();
    }
}
