<?php

namespace CommonModule;

use FKSDB\Components\Controls\DataTesting\PersonTestControl;
use FKSDB\Components\Grids\DataTesting\PersonsGrid;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\DataTesting\DataTestingFactory;

/**
 * Class ValidationPresenter
 * @package OrgModule
 */
class ValidationPresenter extends BasePresenter {

    /**
     * @var ServicePerson
     */
    private $servicePerson;
    /**
     * @var DataTestingFactory
     */
    private $validationFactory;

    /**
     * ValidationPresenter constructor.
     * @param ServicePerson $servicePerson
     */
    public function __construct(ServicePerson $servicePerson) {
        parent::__construct();
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param DataTestingFactory $validationFactory
     */
    public function injectValidationFactory(DataTestingFactory $validationFactory) {
        $this->validationFactory = $validationFactory;
    }

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

    /**
     * @return PersonsGrid
     */
    public function createComponentGrid(): PersonsGrid {
        return new PersonsGrid($this->validationFactory->getTests('person'), $this->getContext());
    }

    /**
     * @return PersonTestControl
     */
    public function createComponentValidationControl(): PersonTestControl {
        return new PersonTestControl($this->getContext(), $this->validationFactory->getTests('person'));
    }
}
