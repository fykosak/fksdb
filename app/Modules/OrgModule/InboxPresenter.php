<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Inbox\Corrected\CorrectedControl;
use FKSDB\Components\Controls\Inbox\HandoutForm;
use FKSDB\Components\Controls\Inbox\Inbox\InboxControl;
use FKSDB\Components\Controls\Inbox\SubmitCheck\SubmitCheckComponent;
use FKSDB\Components\Controls\Inbox\SubmitsPreview\SubmitsPreviewControl;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\UI\PageTitle;
use Nette\Application\BadRequestException;
use FKSDB\Modules\Core\PresenterTraits\{SeriesPresenterTrait};
use FKSDB\Submits\SeriesTable;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Permission;

/**
 * Class InboxPresenter
 *
 */
class InboxPresenter extends BasePresenter implements ISeriesPresenter {
    use SeriesPresenterTrait;

    private SeriesTable $seriesTable;

    final public function injectSeriesTable(SeriesTable $seriesTable): void {
        $this->seriesTable = $seriesTable;
    }

    /* ***************** AUTH ***********************/

    public function authorizedDefault(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', Permission::ALL, $this->getSelectedContest()));
    }

    public function authorizedInbox(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', Permission::ALL, $this->getSelectedContest()));
    }

    public function authorizedList(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'list', $this->getSelectedContest()));
    }

    public function authorizedHandout(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('task', 'edit', $this->getSelectedContest()));
    }

    public function authorizedCorrected(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'corrected', $this->getSelectedContest()));
    }

    /* ***************** TITLES ***********************/

    public function titleInbox(): void {
        $this->setPageTitle(new PageTitle(_('Inbox'), 'fa fa-envelope-open'));
    }

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Inbox dashboard'), 'fa fa-envelope-open'));
    }

    public function titleHandout(): void {
        $this->setPageTitle(new PageTitle(_('Handout'), 'fa fa-inbox'));
    }

    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('List of submits'), 'fa fa-cloud-download'));
    }

    public function titleCorrected(): void {
        $this->setPageTitle(new PageTitle(_('Corrected'), 'fa fa-inbox'));
    }

    /* *********** LIVE CYCLE *************/
    /**
     * @throws ForbiddenRequestException
     * @throws NotImplementedException
     * @throws BadRequestException
     */
    protected function startup(): void {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    public function actionHandout(): void {
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

    protected function beforeRender(): void {
        switch ($this->getAction()) {
            case 'inbox':
                $this->getPageStyleContainer()->setWidePage();
        }
        parent::beforeRender();
    }
}
