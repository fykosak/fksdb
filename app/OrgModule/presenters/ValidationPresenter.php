<?php

namespace OrgModule;

use FKSDB\Components\Controls\Validation\ValidationControl;
use FKSDB\Components\Grids\Validation\ValidationGrid;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ValidationTest\Tests\ParticipantsDuration;
use FKSDB\ValidationTest\Tests\PhoneNumber;

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
     * @var array
     */
    private $availableTests = [PhoneNumber::class, ParticipantsDuration::class];

    /**
     * ValidationPresenter constructor.
     * @param ServicePerson $servicePerson
     */
    public function __construct(ServicePerson $servicePerson) {
        parent::__construct();
        $this->servicePerson = $servicePerson;
    }

    public function titleDefault() {
        $this->setTitle('Validačné testy');
    }

    public function titlePreview() {
        $this->setTitle('Validačné testy');
    }

    /**
     * @return ValidationGrid
     */
    public function createComponentGrid(): ValidationGrid {
        return new ValidationGrid($this->servicePerson, $this->availableTests);
    }

    /**
     * @return ValidationControl
     */
    public function createComponentValidationControl(): ValidationControl {
        return new ValidationControl($this->servicePerson, $this->getTranslator(), $this->availableTests);
    }
}


