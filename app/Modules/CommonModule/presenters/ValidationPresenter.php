<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Components\Controls\DataTesting\PersonTestControl;
use FKSDB\Components\Grids\DataTesting\PersonsGrid;

/**
 * Class ValidationPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ValidationPresenter extends BasePresenter {
    public function titleDefault() {
        $this->setTitle(_('Data validation'), 'fa fa-check');
    }

    public function titleList() {
        $this->setTitle(_('All test'), 'fa fa-check');
    }

    public function titlePreview() {
        $this->setTitle(_('Select test'), 'fa fa-check');
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
