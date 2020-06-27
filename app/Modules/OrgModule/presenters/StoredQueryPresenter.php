<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Entity\StoredQuery\StoredQueryForm;
use FKSDB\Components\Controls\ResultsComponent;
use FKSDB\Components\Controls\StoredQueryTagCloud;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\StoredQuery\StoredQueriesGrid;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use FKSDB\StoredQuery\StoredQuery;
use FKSDB\StoredQuery\StoredQueryFactory;
use FKSDB\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;
use Nette\Utils\Strings;

/**
 * Class StoredQueryPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelStoredQuery getEntity()
 */
class StoredQueryPresenter extends BasePresenter implements ISeriesPresenter {
    use SeriesPresenterTrait;
    use EntityPresenterTrait;

    const PARAM_HTTP_AUTH = 'ha';
    /**
     * @persistent
     */
    public $qid;
    /**
     * @var ServiceStoredQuery
     */
    private $serviceStoredQuery;

    /**
     * @var StoredQueryFactory
     */
    private $storedQueryFactory;
    /**
     * @var StoredQuery
     */
    private $storedQuery;

    /**
     * @param ServiceStoredQuery $serviceStoredQuery
     * @return void
     */
    public function injectServiceStoredQuery(ServiceStoredQuery $serviceStoredQuery) {
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    /**
     * @param StoredQueryFactory $storedQueryFactory
     * @return void
     */
    public function injectStoredQueryFactory(StoredQueryFactory $storedQueryFactory) {
        $this->storedQueryFactory = $storedQueryFactory;
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedExecute() {
        $this->contestAuthorizator->isAllowed($this->getStoredQuery(), 'execute', $this->getSelectedContest());
    }

    public function titleEdit() {
        $this->setPageTitle(new PageTitle(sprintf(_('Úprava dotazu %s'), $this->getEntity()->name), 'fa fa-pencil'));
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(sprintf(_('Create query')), 'fa fa-pencil');
    }

    public function titleList() {
        $this->setPageTitle(new PageTitle(_('Exports'), 'fa fa-database'));
    }

    public function titleDetail() {
        $title = sprintf(_('Detail of the query "%s"'), $this->getEntity()->name);
        $qid = $this->getEntity()->qid;
        if ($qid) {
            $title .= " ($qid)";
        }

        $this->setPageTitle(new PageTitle($title, 'fa fa-database'));
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleExecute() {
        $this->setPageTitle(new PageTitle(sprintf(_('%s'), $this->getStoredQuery()->getName()), 'fa fa-play-circle-o'));
    }


    protected function startup() {
        parent::startup();
        $this->seriesTraitStartup();
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function actionEdit() {
        $this->traitActionEdit();
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function actionExecute() {
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

    public function renderDetail() {
        $this->template->model = $this->getEntity();
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function renderExecute() {
        $this->template->model = $this->getStoredQuery()->getQueryPattern();
    }


    /**
     * @return bool|int|string
     */
    public function getAllowedAuthMethods() {
        $methods = parent::getAllowedAuthMethods();
        if ($this->getParameter(self::PARAM_HTTP_AUTH, false)) {
            $methods = $methods | AuthenticatedPresenter::AUTH_ALLOW_HTTP;
        }
        return $methods;
    }

    /**
     * @return string
     */
    protected function getHttpRealm() {
        return 'FKSDB-export';
    }

    /**
     * @return StoredQuery
     * @throws BadRequestException
     */
    public function getStoredQuery(): StoredQuery {
        if (!isset($this->storedQuery) || is_null($this->storedQuery)) {
            $model = $this->getQueryByQId();
            if (!$model) {
                $model = $this->getEntity();
            }
            $this->storedQuery = $this->storedQueryFactory->createQuery($this, $model);
        }
        return $this->storedQuery;
    }

    /**
     * @return ModelStoredQuery|null
     */
    public function getQueryByQId() {
        $qid = $this->getParameter('qid');
        if ($qid) {
            return $this->serviceStoredQuery->findByQid($qid);
        }
        return null;
    }

    protected function createComponentCreateForm(): Control {
        return new StoredQueryForm($this->getContext(), true);
    }

    protected function createComponentEditForm(): Control {
        return new StoredQueryForm($this->getContext(), true);
    }

    protected function createComponentGrid(): BaseGrid {
        /** @var StoredQueryTagCloud $cloud */
        $cloud = $this->getComponent('tagCloud');
        return new StoredQueriesGrid($this->getContext(), $cloud->activeTagIds);
    }

    protected function createComponentTagCloud(): StoredQueryTagCloud {
        return new StoredQueryTagCloud($this->getContext());
    }

    /**
     * @return ResultsComponent
     * @throws BadRequestException
     */
    protected function createComponentResultsComponent(): ResultsComponent {
        $control = new ResultsComponent($this->getContext());
        $control->setStoredQuery($this->getStoredQuery());
        return $control;
    }

    protected function getORMService(): ServiceStoredQuery {
        return $this->serviceStoredQuery;
    }


    /**
     * @param IResource|string $resource
     * @param string|null $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->getContestAuthorizator()->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    /**
     * @param PageTitle $pageTitle
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function setPageTitle(PageTitle $pageTitle) {
        $pageTitle->subTitle .= ' ' . sprintf(_('%d. series'), $this->getSelectedSeries());
        parent::setPageTitle($pageTitle);
    }
}
