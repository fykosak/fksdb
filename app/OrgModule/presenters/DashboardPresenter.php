<?php

namespace OrgModule;

use ServiceSubmit;

/**
 * Homepage presenter.
 */
class DashboardPresenter extends BasePresenter {

    /**
     * @var ServiceSubmit
     */
    private $serviceSubmit;

    public function injectServiceSubmit(ServiceSubmit $serviceSubmit) {
        $this->serviceSubmit = $serviceSubmit;
    }

    public function titleDefault() {
        $this->setTitle(_('Organizátorský pultík'));
    }

    public function renderDefault() {
        
    }

}
