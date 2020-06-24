<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Entity\StoredQuery\StoredQueryForm;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\StoredQueries2Grid;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use FKSDB\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class StoredQueryPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StoredQueryPresenter extends BasePresenter implements ISeriesPresenter {
    use SeriesPresenterTrait;
    use EntityPresenterTrait;

    /**
     * @var ServiceStoredQuery
     */
    private $serviceStoredQuery;

    /**
     * @param ServiceStoredQuery $serviceStoredQuery
     * @return void
     */
    public function injectServiceStoredQuery(ServiceStoredQuery $serviceStoredQuery) {
        $this->serviceStoredQuery = $serviceStoredQuery;
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

    protected function createComponentCreateForm(): Control {
        return new StoredQueryForm($this->getContext(), true);
    }

    protected function createComponentEditForm(): Control {
        return new StoredQueryForm($this->getContext(), true);
    }

    protected function createComponentGrid(): BaseGrid {
        return new StoredQueries2Grid($this->getContext());
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
