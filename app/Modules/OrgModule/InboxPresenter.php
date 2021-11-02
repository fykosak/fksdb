<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Inbox\Corrected\CorrectedComponent;
use FKSDB\Components\Controls\Inbox\HandoutFormComponent;
use FKSDB\Components\Controls\Inbox\Inbox\InboxFormComponent;
use FKSDB\Components\Controls\Inbox\SubmitCheck\SubmitCheckComponent;
use FKSDB\Components\Controls\Inbox\SubmitsPreview\SubmitsPreviewComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Submits\SeriesTable;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\{SeriesPresenterTrait};
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Permission;

class InboxPresenter extends BasePresenter
{
    use SeriesPresenterTrait;

    private SeriesTable $seriesTable;

    final public function injectSeriesTable(SeriesTable $seriesTable): void
    {
        $this->seriesTable = $seriesTable;
    }

    /* ***************** AUTH ***********************/

    public function authorizedInbox(): void
    {
        $this->setAuthorized(
            $this->contestAuthorizator->isAllowed('submit', Permission::ALL, $this->getSelectedContest())
        );
    }

    public function authorizedList(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'list', $this->getSelectedContest()));
    }

    public function authorizedHandout(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('task', 'edit', $this->getSelectedContest()));
    }

    public function authorizedCorrected(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'corrected', $this->getSelectedContest()));
    }

    /* ***************** TITLES ***********************/

    public function titleInbox(): PageTitle
    {
        return new PageTitle(_('Inbox'), 'fa fa-envelope');
    }

    public function titleHandout(): PageTitle
    {
        return new PageTitle(_('Handout'), 'fa fa-folder-open');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('List of submits'), 'fa fa-list-ul');
    }

    public function titleCorrected(): PageTitle
    {
        return new PageTitle(_('Corrected'), 'fa fa-file-signature');
    }

    /* *********** LIVE CYCLE *************/

    /**
     * @throws BadTypeException
     */
    public function actionHandout(): void
    {
        /** @var HandoutFormComponent $control */
        $control = $this->getComponent('handoutForm');
        $control->setDefaults();

        // This workaround fixes inproper caching of referenced tables.
        // $connection = $this->servicePerson->getConnection();
        // $connection->getCache()->clean(array(Cache::ALL => true));
        // $connection->getDatabaseReflection()->setConnection($connection);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws BadRequestException
     */
    protected function startup(): void
    {
        parent::startup();
        $this->seriesTable->setContestYear($this->getSelectedContestYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    /* ******************* COMPONENTS ******************/

    protected function createComponentInboxForm(): InboxFormComponent
    {
        return new InboxFormComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentHandoutForm(): HandoutFormComponent
    {
        return new HandoutFormComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentCorrectedFormControl(): CorrectedComponent
    {
        return new CorrectedComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentCheckControl(): SubmitCheckComponent
    {
        return new SubmitCheckComponent($this->getContext(), $this->seriesTable);
    }

    protected function createComponentSubmitsTableControl(): SubmitsPreviewComponent
    {
        return new SubmitsPreviewComponent($this->getContext(), $this->seriesTable);
    }

    protected function beforeRender(): void
    {
        switch ($this->getAction()) {
            case 'inbox':
                $this->getPageStyleContainer()->setWidePage();
        }
        parent::beforeRender();
    }
}
