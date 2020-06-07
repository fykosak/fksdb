<?php

namespace FKSDB\Components\Controls\Entity\School;

use FKSDB\Components\Controls\Entity\AbstractEntityFormControl;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServiceSchool;
use Nette\Application\UI\Form;

/**
 * Class AbstractForm
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractForm extends AbstractEntityFormControl {

    const CONT_ADDRESS = 'address';
    const CONT_SCHOOL = 'school';

    /** @var ServiceAddress */
    protected $serviceAddress;

    /** @var ServiceSchool */
    protected $serviceSchool;

    /** @var SchoolFactory */
    protected $schoolFactory;

    /** @var AddressFactory */
    protected $addressFactory;

    /**
     * @param AddressFactory $addressFactory
     * @param SchoolFactory $schoolFactory
     * @param ServiceAddress $serviceAddress
     * @param ServiceSchool $serviceSchool
     * @return void
     */
    public function injectPrimary(
        AddressFactory $addressFactory,
        SchoolFactory $schoolFactory,
        ServiceAddress $serviceAddress,
        ServiceSchool $serviceSchool
    ) {
        $this->addressFactory = $addressFactory;
        $this->schoolFactory = $schoolFactory;
        $this->serviceAddress = $serviceAddress;
        $this->serviceSchool = $serviceSchool;
    }

    /**
     * @param Form $form
     * @return void
     */
    protected function configureForm(Form $form) {
        $schoolContainer = $this->schoolFactory->createSchool();
        $form->addComponent($schoolContainer, self::CONT_SCHOOL);

        $addressContainer = $this->addressFactory->createAddress(AddressFactory::REQUIRED | AddressFactory::NOT_WRITEONLY);
        $form->addComponent($addressContainer, self::CONT_ADDRESS);
    }
}
