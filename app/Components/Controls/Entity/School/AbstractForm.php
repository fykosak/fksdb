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
 * @package FKSDB\Components\Controls\Entity\School
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
        parent::__construct();
        $this->addressFactory = $container->getByType(AddressFactory::class);
        $this->schoolFactory = $container->getByType(SchoolFactory::class);
        $this->serviceAddress = $container->getByType(ServiceAddress::class);
        $this->serviceSchool = $container->getByType(ServiceSchool::class);
        $this->buildForm();
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
