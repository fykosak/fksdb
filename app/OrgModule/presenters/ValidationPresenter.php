<?php

namespace OrgModule;

use FKSDB\Components\Controls\Validation\ValidationControl;
use FKSDB\Components\Grids\Validation\ValidationGrid;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ValidationTest\Tests\GenderFromBornNumber;
use FKSDB\ValidationTest\Tests\ParticipantsDuration;
use FKSDB\ValidationTest\Tests\PhoneNumber;
use FKSDB\ValidationTest\ValidationFactory;
use FKSDB\ValidationTest\ValidationTest;

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
     * @var ValidationTest[]
     */
    public static $availableTests = [PhoneNumber::class, ParticipantsDuration::class, GenderFromBornNumber::class];
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

    /**
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedDefault() {
        $this->setAuthorized(
            $this->getContestAuthorizator()->isAllowed('person', 'validation', $this->getSelectedContest()));
    }

    /**
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedList() {
        return $this->authorizedDefault();
    }

    /**
     * @throws \Nette\Application\BadRequestException
     */
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


