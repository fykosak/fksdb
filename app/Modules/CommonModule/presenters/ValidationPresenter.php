<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Components\Controls\DataTesting\PersonTestControl;
use FKSDB\Components\Grids\DataTesting\PersonsGrid;
use FKSDB\UI\PageTitle;

/**
 * Class ValidationPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ValidationPresenter extends BasePresenter {
    public function titleDefault() {
        $this->setPageTitle(new PageTitle(_('Data validation'), 'fa fa-check'));
    }

    public function titleList() {
        $this->setPageTitle(new PageTitle(_('All test'), 'fa fa-check'));
    }

    public function titlePreview() {
        $this->setPageTitle(new PageTitle(_('Select test'), 'fa fa-check'));
    }

    public function authorizedDefault() {
        $this->setAuthorized(
            $this->getContestAuthorizator()->isAllowedForAnyContest('person', 'validation'));
    }

    public function authorizedList() {
        return $this->authorizedDefault();
    }

    public function authorizedPreview() {
        return $this->authorizedDefault();
    }

    protected function createComponentGrid(): PersonsGrid {
        return new PersonsGrid($this->getContext());
    }

    protected function createComponentValidationControl(): PersonTestControl {
        return new PersonTestControl($this->getContext());
    }
}
