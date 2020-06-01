<?php

namespace FKSDB\Components\Controls\Entity\School;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServiceSchool;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 * Class AbstractForm
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractForm extends FormControl {

    public const CONT_ADDRESS = 'address';
    public const CONT_SCHOOL = 'school';

    protected ServiceAddress $serviceAddress;

    protected ServiceSchool $serviceSchool;

    protected SchoolFactory $schoolFactory;

    protected AddressFactory $addressFactory;

    /**
     * AbstractForm constructor.
     * @param Container $container
     * @throws BadRequestException
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->buildForm();
    }

    public function injectPrimary(AddressFactory $addressFactory, SchoolFactory $schoolFactory, ServiceAddress $serviceAddress, ServiceSchool $serviceSchool): void {
        $this->addressFactory = $addressFactory;
        $this->schoolFactory = $schoolFactory;
        $this->serviceAddress = $serviceAddress;
        $this->serviceSchool = $serviceSchool;
    }

    /**
     * @throws BadRequestException
     */
    protected function buildForm(): void {
        $form = $this->getForm();
        $schoolContainer = $this->schoolFactory->createSchool();
        $form->addComponent($schoolContainer, self::CONT_SCHOOL);

        $addressContainer = $this->addressFactory->createAddress(AddressFactory::REQUIRED | AddressFactory::NOT_WRITEONLY);
        $form->addComponent($addressContainer, self::CONT_ADDRESS);
    }

}
