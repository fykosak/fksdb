<?php

namespace FKSDB\Components\Forms\Containers\Models;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Components\Forms\Containers\AddressContainer;
use FKSDB\Components\Forms\Containers\IWriteOnly;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\FlagFactory;
use FKSDB\Components\Forms\Factories\PersonScheduleFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Application\BadRequestException;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Utils\JsonException;
use Persons\IModifiabilityResolver;
use Persons\IVisibilityResolver;
use Persons\ReferencedPersonHandler;

/**
 * Class ReferencedPersonContainer
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ReferencedPersonContainer extends ReferencedContainer {

    const TARGET_FORM = 0x1;
    const TARGET_VALIDATION = 0x2;
    const EXTRAPOLATE = 0x4;
    const HAS_DELIVERY = 0x8;

    /** @var IModifiabilityResolver */
    public $modifiabilityResolver;
    /** @var IVisibilityResolver */
    public $visibilityResolver;
    /** @var int */
    private $acYear;
    /** @var array */
    private $fieldsDefinition;

    /**
     * @var ServicePerson
     */
    protected $servicePerson;

    /**
     * @var SingleReflectionFormFactory
     */
    protected $singleReflectionFormFactory;

    /**
     * @var FlagFactory
     */
    protected $flagFactory;
    /**
     * @var AddressFactory
     */
    protected $addressFactory;
    /**
     * @var PersonScheduleFactory
     */
    private $personScheduleFactory;
    /**
     * @var ModelEvent
     */
    protected $event;


    /**
     * ReferencedPersonContainer constructor.
     * @param Container $container
     * @param IModifiabilityResolver $modifiabilityResolver
     * @param IVisibilityResolver $visibilityResolver
     * @param int $acYear
     * @param array $fieldsDefinition
     * @param ModelEvent|null $event
     * @param bool $allowClear
     * @throws AbstractColumnException
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws OmittedControlException
     */
    public function __construct(
        Container $container,
        IModifiabilityResolver $modifiabilityResolver,
        IVisibilityResolver $visibilityResolver,
        int $acYear,
        array $fieldsDefinition,
        $event,
        bool $allowClear
    ) {
        parent::__construct($container, $allowClear);
        $this->modifiabilityResolver = $modifiabilityResolver;
        $this->visibilityResolver = $visibilityResolver;
        $this->acYear = $acYear;
        $this->fieldsDefinition = $fieldsDefinition;
        $this->event = $event;
    }

    /**
     * AbstractReferencedPersonFactory constructor.
     * @param AddressFactory $addressFactory
     * @param FlagFactory $flagFactory
     * @param ServicePerson $servicePerson
     * @param SingleReflectionFormFactory $singleReflectionFormFactory
     * @param PersonScheduleFactory $personScheduleFactory
     */
    public function injectPrimary(
        AddressFactory $addressFactory,
        FlagFactory $flagFactory,
        ServicePerson $servicePerson,
        SingleReflectionFormFactory $singleReflectionFormFactory,
        PersonScheduleFactory $personScheduleFactory
    ) {
        $this->servicePerson = $servicePerson;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->flagFactory = $flagFactory;
        $this->addressFactory = $addressFactory;
        $this->personScheduleFactory = $personScheduleFactory;
    }

    /**
     * @return void
     * @throws AbstractColumnException
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws OmittedControlException
     */
    protected function configure() {
    }

    /**
     * @param IModel|ModelPerson|null $model
     * @param string $mode
     * @return void
     */
    public function setModel(IModel $model = null, string $mode = ReferencedId::MODE_NORMAL) {
        $this->getReferencedId()->referencedSetter->setModel($this, $model, $mode, $this->event);
    }
}
