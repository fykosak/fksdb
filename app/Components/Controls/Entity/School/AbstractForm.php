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
    protected function buildForm() {
        $form = $this->getForm();
        $schoolContainer = $this->schoolFactory->createSchool();
        $form->addComponent($schoolContainer, self::CONT_SCHOOL);

        $addressContainer = $this->addressFactory->createAddress(AddressFactory::REQUIRED | AddressFactory::NOT_WRITEONLY);
        $form->addComponent($addressContainer, self::CONT_ADDRESS);
    }

}
