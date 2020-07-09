<?php

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\PersonScheduleFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
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

    const TARGET_VALIDATION = 0x2;
    const EXTRAPOLATE = 0x4;
    const HAS_DELIVERY = 0x8;

    /**
     * @var ServicePerson
     */
    protected $servicePerson;
    /**
     * @var ReferencedPersonHandlerFactory
     */
    protected $referencedPersonHandlerFactory;
    /**
     * @var PersonScheduleFactory
     */
    private $personScheduleFactory;

    /** @var Container */
    private $context;

    /**
     * AbstractReferencedPersonFactory constructor.
     * @param ServicePerson $servicePerson
     * @param ReferencedPersonHandlerFactory $referencedPersonHandlerFactory
     * @param PersonScheduleFactory $personScheduleFactory
     * @param Container $context
     */
    public function __construct(
        ServicePerson $servicePerson,
        ReferencedPersonHandlerFactory $referencedPersonHandlerFactory,
        PersonScheduleFactory $personScheduleFactory,
        Container $context
    ) {
        $this->servicePerson = $servicePerson;
        $this->referencedPersonHandlerFactory = $referencedPersonHandlerFactory;
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
     * @throws AbstractColumnException
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws OmittedControlException
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
     * @param ModelEvent|null $event
     * @return bool
     * @throws JsonException
     */
    final public function isFilled(ModelPerson $person, string $sub, string $field, int $acYear, $event = null): bool {
        $value = $this->getPersonValue($person, $sub, $field, $acYear, $event, self::TARGET_VALIDATION);
        return !($value === null || $value === '');
    }

    /**
     * @param ModelPerson|null $person
     * @param string $sub
     * @param string $field
     * @param int $acYear
     * @param ModelEvent|null $event
     * @param int $options
     * @return bool|ModelPostContact|mixed|null
     * @throws JsonException
     */
    protected function getPersonValue($person, string $sub, string $field, int $acYear, $event, $options) {
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
                return ($history = $person->getHistory($acYear, (bool)($options & self::EXTRAPOLATE))) ? $history[$field] : null;
            case 'post_contact_d':
                return $person->getDeliveryAddress();
            case 'post_contact_p':
                if (($options & self::TARGET_VALIDATION) || !($options & self::HAS_DELIVERY)) {
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
