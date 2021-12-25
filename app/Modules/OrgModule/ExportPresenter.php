<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\StoredQuery\ResultsComponent;
use FKSDB\Components\Controls\StoredQuery\StoredQueryTagCloudComponent;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQuery;
use FKSDB\Models\StoredQuery\StoredQuery;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;
use Nette\Utils\Strings;

/**
 * @method ModelStoredQuery getEntity()
 */
class ExportPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private const PARAM_HTTP_AUTH = 'ha';
    private ServiceStoredQuery $serviceStoredQuery;
    private StoredQueryFactory $storedQueryFactory;
    private StoredQuery $storedQuery;

    final public function injectServiceStoredQuery(
        ServiceStoredQuery $serviceStoredQuery,
        StoredQueryFactory $storedQueryFactory
    ): void {
        $this->serviceStoredQuery = $serviceStoredQuery;
        $this->storedQueryFactory = $storedQueryFactory;
    }

    /**
     * @throws BadRequestException
     * @throws ModelNotFoundException
     */
    public function authorizedExecute(): void
    {
        $this->contestAuthorizator->isAllowed($this->getStoredQuery(), 'execute', $this->getSelectedContest());
    }

    /**
     * @throws BadRequestException
     * @throws ModelNotFoundException
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

    public function getQueryByQId(): ?ModelStoredQuery
    {
        $qid = $this->getParameter('qid');
        if ($qid) {
            return $this->serviceStoredQuery->findByQid($qid);
        }
        return null;
    }

    /**
     * @throws BadRequestException
     * @throws ModelNotFoundException
     */
    public function titleExecute(): PageTitle
    {
        return new PageTitle(sprintf(_('%s'), $this->getStoredQuery()->getName()), 'fa fa-play-circle');
    }

    /**
     * @throws BadRequestException
     * @throws ModelNotFoundException
     */
    public function actionExecute(): void
    {
        $storedQuery = $this->getStoredQuery();
        if ($storedQuery && $this->getParameter('qid')) {
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
     * @throws ModelNotFoundException
     */
    final public function renderExecute(): void
    {
        $this->template->model = $this->getStoredQuery()->getQueryPattern();
    }

    public function getAllowedAuthMethods(): array
    {
        $methods = parent::getAllowedAuthMethods();
        if ($this->getParameter(self::PARAM_HTTP_AUTH, false)) {
            $methods[self::AUTH_HTTP] = true;
        }
        return $methods;
    }

    protected function startup(): void
    {
        switch ($this->getAction()) {
            case 'edit':
                $this->redirect(':Org:StoredQuery:edit', $this->getParameters());
                break;
            case 'compose':
                $this->redirect(':Org:StoredQuery:create', $this->getParameters());
                break;
            case 'list':
                $this->forward(':Org:StoredQuery:list', $this->getParameters()); // forward purposely
                break;
            case 'show':
                $this->redirect(':Org:StoredQuery:detail', $this->getParameters());
        }
        parent::startup();
    }

    protected function getHttpRealm(): ?string
    {
        return 'FKSDB-export';
    }

    /**
     * @throws BadRequestException
     * @throws ModelNotFoundException
     */
    protected function createComponentResultsComponent(): ResultsComponent
    {
        $control = new ResultsComponent($this->getContext());
        $control->setStoredQuery($this->getStoredQuery());
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

    protected function getORMService(): ServiceStoredQuery
    {
        return $this->serviceStoredQuery;
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return false;
    }

    protected function createComponentGrid(): BaseGrid
    {
        throw new NotImplementedException();
    }
}
