<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Model\Entity\ModelNotFoundException;
use FKSDB\Model\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Model\StoredQuery\StoredQuery;
use FKSDB\Model\StoredQuery\StoredQueryFactory;
use FKSDB\Components\Controls\StoredQuery\ResultsComponent;
use FKSDB\Components\Controls\StoredQuery\StoredQueryTagCloud;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use Nette\Application\AbortException;
use FKSDB\Model\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Model\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\Model\ORM\Services\StoredQuery\ServiceStoredQuery;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;
use Nette\Utils\Strings;

/**
 * Class ExportPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelStoredQuery getEntity()
 */
class ExportPresenter extends BasePresenter implements ISeriesPresenter {

    use EntityPresenterTrait;

    private const PARAM_HTTP_AUTH = 'ha';

    /**
     * @persistent
     */
    public $qid;

    private ServiceStoredQuery $serviceStoredQuery;
    private StoredQueryFactory $storedQueryFactory;
    private StoredQuery $storedQuery;

    final public function injectServiceStoredQuery(ServiceStoredQuery $serviceStoredQuery, StoredQueryFactory $storedQueryFactory): void {
        $this->serviceStoredQuery = $serviceStoredQuery;
        $this->storedQueryFactory = $storedQueryFactory;
    }

    protected function startup(): void {
        switch ($this->getAction()) {
            case 'edit':
                $this->redirect(':Org:StoredQuery:edit', $this->getParameters());
            case 'compose':
                $this->redirect(':Org:StoredQuery:create', $this->getParameters());
            case 'list':
                $this->forward(':Org:StoredQuery:list', $this->getParameters()); // forward purposely
            case 'show':
                $this->redirect(':Org:StoredQuery:detail', $this->getParameters());
        }
        parent::startup();
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ModelNotFoundException
     */
    public function authorizedExecute(): void {
        $this->contestAuthorizator->isAllowed($this->getStoredQuery(), 'execute', $this->getSelectedContest());
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws ModelNotFoundException
     */
    public function titleExecute(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('%s'), $this->getStoredQuery()->getName()), 'fa fa-play-circle-o'));
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ModelNotFoundException
     */
    public function actionExecute(): void {
        $storedQuery = $this->getStoredQuery();
        if ($storedQuery && $this->getParameter('qid')) {
            $parameters = [];
            foreach ($this->getParameters() as $key => $value) {
                if (Strings::startsWith($key, ResultsComponent::PARAMETER_URL_PREFIX)) {
                    $parameters[substr($key, strlen(ResultsComponent::PARAMETER_URL_PREFIX))] = $value;
                }
            }
        }
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ModelNotFoundException
     */
    public function renderExecute(): void {
        $this->template->model = $this->getStoredQuery()->getQueryPattern();
    }

    public function getAllowedAuthMethods(): int {
        $methods = parent::getAllowedAuthMethods();
        if ($this->getParameter(self::PARAM_HTTP_AUTH, false)) {
            $methods = $methods | AuthenticatedPresenter::AUTH_ALLOW_HTTP;
        }
        return $methods;
    }

    protected function getHttpRealm(): ?string {
        return 'FKSDB-export';
    }

    /**
     * @return StoredQuery
     * @throws BadRequestException
     * @throws ModelNotFoundException
     */
    public function getStoredQuery(): StoredQuery {
        if (!isset($this->storedQuery)) {
            $model = $this->getQueryByQId();
            if (!$model) {
                $model = $this->getEntity();
            }
            $this->storedQuery = $this->storedQueryFactory->createQuery($this, $model);
        }
        return $this->storedQuery;
    }

    public function getQueryByQId(): ?ModelStoredQuery {
        $qid = $this->getParameter('qid');
        if ($qid) {
            return $this->serviceStoredQuery->findByQid($qid);
        }
        return null;
    }

    /**
     * @return ResultsComponent
     * @throws BadRequestException
     * @throws ModelNotFoundException
     */
    protected function createComponentResultsComponent(): ResultsComponent {
        $control = new ResultsComponent($this->getContext());
        $control->setStoredQuery($this->getStoredQuery());
        return $control;
    }

    protected function createComponentTagCloud(): StoredQueryTagCloud {
        return new StoredQueryTagCloud($this->getContext());
    }

    /**
     * @param PageTitle $pageTitle
     * @return void
     * @throws AbortException
     */
    protected function setPageTitle(PageTitle $pageTitle): void {
        $pageTitle->subTitle .= ' ' . sprintf(_('%d. series'), $this->getSelectedSeries());
        parent::setPageTitle($pageTitle);
    }

    protected function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    protected function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

    protected function getORMService(): ServiceStoredQuery {
        return $this->serviceStoredQuery;
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return false;
    }

    protected function createComponentGrid(): BaseGrid {
        throw new NotImplementedException();
    }
}