<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\DataTesting\PersonTestComponent;
use FKSDB\Components\Grids\DataTesting\PersonsGrid;
use FKSDB\Models\UI\PageTitle;

/**
 * Class ValidationPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ValidationPresenter extends BasePresenter {
    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Data validation'), 'fas fa-check'));
    }

    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('All test'), 'fas fa-check'));
    }

    public function titlePreview(): void {
        $this->setPageTitle(new PageTitle(_('Select test'), 'fas fa-check'));
    }

    public function authorizedDefault(): void {
        $this->setAuthorized(
            $this->contestAuthorizator->isAllowedForAnyContest('person', 'validation'));
    }

    public function authorizedList(): void {
        $this->authorizedDefault();
    }

    public function authorizedPreview(): void {
        $this->authorizedDefault();
    }

    protected function createComponentGrid(): PersonsGrid {
        return new PersonsGrid($this->getContext());
    }

    protected function createComponentValidationControl(): PersonTestComponent {
        return new PersonTestComponent($this->getContext());
    }
}
