<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Entity\StoredQuery\StoredQueryForm;
use FKSDB\Components\Controls\StoredQueryTagCloud;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\StoredQuery\StoredQueriesGrid;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use FKSDB\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class StoredQueryPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelStoredQuery getEntity()
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


    protected function startup() {
        switch ($this->getAction()) {
            case 'execute':
                $this->redirect(':Org:Export:execute', $this->getParameters());
        }
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

    public function renderDetail() {
        $this->template->model = $this->getEntity();
    }

    protected function createComponentCreateForm(): Control {
        return new StoredQueryForm($this->getContext(), true);
    }

    protected function createComponentEditForm(): Control {
        return new StoredQueryForm($this->getContext(), false);
    }

    protected function createComponentGrid(): BaseGrid {
        /** @var StoredQueryTagCloud $cloud */
        $cloud = $this->getComponent('tagCloud');
        return new StoredQueriesGrid($this->getContext(), $cloud->activeTagIds);
    }

    protected function createComponentTagCloud(): StoredQueryTagCloud {
        return new StoredQueryTagCloud($this->getContext());
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
