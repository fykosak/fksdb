<?php

namespace CommonModule;

use FKSDB\Components\Controls\Validation\ValidationControl;
use FKSDB\Components\Grids\Validation\ValidationGrid;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ValidationTest\ValidationFactory;

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
     * @var ValidationFactory
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
     * @param ValidationFactory $validationFactory
     */
    public function injectValidationFactory(ValidationFactory $validationFactory) {
        $this->validationFactory = $validationFactory;
    }

    public function titleDefault() {
        $this->setIcon('fa fa-check');
        $this->setTitle('Validation tests');
    }

    public function titleList() {
        $this->setIcon('fa fa-check');
        $this->setTitle('All test');
    }

    public function titlePreview() {
        $this->setIcon('fa fa-check');
        $this->setTitle('Select test');
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
     * @return ValidationGrid
     */
    public function createComponentGrid(): ValidationGrid {
        return new ValidationGrid($this->servicePerson, $this->validationFactory->getTests());
    }

    /**
     * @return ValidationControl
     */
    public function createComponentValidationControl(): ValidationControl {
        return new ValidationControl($this->servicePerson, $this->getTranslator(), $this->validationFactory->getTests());
    }
}


