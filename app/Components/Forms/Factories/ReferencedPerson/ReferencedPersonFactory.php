<?php

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\PersonScheduleFactory;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Services\ServicePerson;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\JsonException;
use Persons\IModifiabilityResolver;
use Persons\IVisibilityResolver;
use Persons\ReferencedPersonHandlerFactory;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedPersonFactory {
    use SmartObject;

    /**
     * @var ServicePerson
     */
    protected $servicePerson;

    /**
     * @var PersonFactory
     */
    protected $personFactory;


    /**
     * @var ReferencedPersonHandlerFactory
     */
    protected $referencedPersonHandlerFactory;

    /**
     * @var PersonProvider
     */
    protected $personProvider;

    /**
     * @var PersonScheduleFactory
     */
    private $personScheduleFactory;
    /**
     * @var ModelEvent
     */
    private $event;
    /** @var \Nette\DI\Container */
    private $context;

    /**
     * AbstractReferencedPersonFactory constructor.
     * @param ServicePerson $servicePerson
     * @param PersonFactory $personFactory
     * @param ReferencedPersonHandlerFactory $referencedPersonHandlerFactory
     * @param PersonProvider $personProvider
     * @param PersonScheduleFactory $personScheduleFactory
     * @param \Nette\DI\Container $context
     */
    public function __construct(
        ServicePerson $servicePerson,
        PersonFactory $personFactory,
        ReferencedPersonHandlerFactory $referencedPersonHandlerFactory,
        PersonProvider $personProvider,
        PersonScheduleFactory $personScheduleFactory,
        \Nette\DI\Container $context
    ) {
        $this->servicePerson = $servicePerson;
        $this->personFactory = $personFactory;
        $this->referencedPersonHandlerFactory = $referencedPersonHandlerFactory;
        $this->personProvider = $personProvider;
        $this->personScheduleFactory = $personScheduleFactory;
        $this->context = $context;
    }

    /**
     * @param array $fieldsDefinition
     * @param int $acYear
     * @param string $searchType
     * @param bool $allowClear
     * @param IModifiabilityResolver $modifiabilityResolver
     * @param IVisibilityResolver $visibilityResolver
     * @param ModelEvent|null $event
     * @return ReferencedId
     */
    public function createReferencedPerson(
        array $fieldsDefinition,
        int $acYear,
        string $searchType,
        bool $allowClear,
        IModifiabilityResolver $modifiabilityResolver,
        IVisibilityResolver $visibilityResolver,
        $event = null
    ): ReferencedId {

        $handler = $this->referencedPersonHandlerFactory->create($acYear, null, $event);
        return new ReferencedId(
            new PersonSearchContainer($this->context, $searchType),
            new ReferencedPersonContainer($this->context, $modifiabilityResolver, $visibilityResolver, $acYear, $fieldsDefinition, $event, $allowClear),
            $this->servicePerson,
            $handler
        );
    }

    /**
     * @param ModelPerson $person
     * @param string $sub
     * @param string $field
     * @param int $acYear
     * @param null $event
     * @return bool
     * @throws JsonException
     */
    final public static function isFilled(ModelPerson $person, string $sub, string $field, int $acYear, $event = null): bool {
        $value = self::getPersonValue($person, $sub, $field, $acYear, ReferencedPersonContainer::TARGET_VALIDATION, $event);
        return !($value === null || $value === '');
    }

    /**
     * @param ModelPerson|null $person
     * @param string $sub
     * @param string $field
     * @param int $acYear
     * @param int $options
     * @param ModelEvent|null $event
     * @return bool|ModelPostContact|mixed|null
     * @throws JsonException
     */
    public static function getPersonValue($person, string $sub, string $field, int $acYear, $options, $event = null) {
        if (!$person) {
            return null;
        }
        switch ($sub) {
            case 'person_schedule':
                return $person->getSerializedSchedule($event->event_id, $field);
            case 'person':
                return $person[$field];
            case 'person_info':
                $result = ($info = $person->getInfo()) ? $info[$field] : null;
                if ($field == 'agreed') {
                    // See isFilled() semantics. We consider those who didn't agree as NOT filled.
                    $result = $result ? true : null;
                }
                return $result;
            case 'person_history':
                return ($history = $person->getHistory($acYear, (bool)($options & ReferencedPersonContainer::EXTRAPOLATE))) ? $history[$field] : null;
            case 'post_contact_d':
                return $person->getDeliveryAddress();
            case 'post_contact_p':
                if (($options & ReferencedPersonContainer::TARGET_VALIDATION) || !($options & ReferencedPersonContainer::HAS_DELIVERY)) {
                    return $person->getPermanentAddress();
                }
                return $person->getPermanentAddress(true);
            case 'person_has_flag':
                return ($flag = $person->getMPersonHasFlag($field)) ? (bool)$flag['value'] : null;
            default:
                throw new InvalidArgumentException("Unknown person sub '$sub'.");
        }
    }
}
