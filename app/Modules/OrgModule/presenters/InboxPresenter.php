<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Inbox\CorrectedControl;
use FKSDB\Components\Controls\Inbox\HandoutForm;
use FKSDB\Components\Controls\Inbox\SubmitsPreviewControl;
use FKSDB\Components\Controls\Inbox\SubmitCheckComponent;
use FKSDB\Components\Controls\Inbox\InboxControl;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\{SeriesPresenterTrait};
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Permission;

/**
 * Class InboxPresenter
 * *
 */
class InboxPresenter extends BasePresenter implements ISeriesPresenter {

    use SeriesPresenterTrait;

    /**
     * @var SeriesTable
     */
    private $seriesTable;

    /**
     * @param SeriesTable $seriesTable
     * @return void
     */
    public function injectSeriesTable(SeriesTable $seriesTable) {
        $this->seriesTable = $seriesTable;
    }

    /* ***************** AUTH ***********************/

    /**
     * @return void
     * @throws BadRequestException
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest()));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedInbox() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest()));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'list', $this->getSelectedContest()));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedHandout() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('task', 'edit', $this->getSelectedContest()));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedCorrected() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'corrected', $this->getSelectedContest()));
    }

    /* ***************** TITLES ***********************/
    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleInbox() {
        $this->setPageTitle(new PageTitle(_('Inbox'), 'fa fa-envelope-open'));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleDefault() {
        $this->setPageTitle(new PageTitle(_('Inbox dashboard'), 'fa fa-envelope-open'));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleHandout() {
        $this->setPageTitle(new PageTitle(_('Rozdělení úloh opravovatelům'), 'fa fa-inbox'));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList() {
        $this->setPageTitle(new PageTitle(_('List of submits'), 'fa fa-cloud-download'));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleCorrected() {
        $this->setPageTitle(new PageTitle(_('Corrected'), 'fa fa-inbox'));
    }

    /* *********** LIVE CYCLE *************/
    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function startup() {
        parent::startup();
        $this->seriesTraitStartup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    public function actionHandout() {
        /** @var HandoutForm $control */
        $control = $this->getComponent('handoutForm');
        $control->setDefaults();

        // This workaround fixes inproper caching of referenced tables.
        // $connection = $this->servicePerson->getConnection();
        // $connection->getCache()->clean(array(Cache::ALL => true));
        // $connection->getDatabaseReflection()->setConnection($connection);
    }

    /* ******************* COMPONENTS ******************/

    protected function createComponentInboxForm(): InboxControl {
        return new InboxControl($this->getContext(), $this->seriesTable);
    }

    protected function createComponentHandoutForm(): HandoutForm {
        return new HandoutForm($this->getContext(), $this->seriesTable);
    }

    protected function createComponentCorrectedFormControl(): CorrectedControl {
        return new CorrectedControl($this->getContext(), $this->seriesTable);
    }

    protected function createComponentCheckControl(): SubmitCheckComponent {
        return new SubmitCheckComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentSubmitsTableControl(): SubmitsPreviewControl {
        return new SubmitsPreviewControl($this->getContext(), $this->seriesTable);
    }

    protected function beforeRender() {
        switch ($this->getAction()) {
            case 'inbox':
                $this->getPageStyleContainer()->mainContainerClassName = str_replace('container ', 'container-fluid ', $this->getPageStyleContainer()->mainContainerClassName) . ' px-3';
        }
        parent::beforeRender();
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
