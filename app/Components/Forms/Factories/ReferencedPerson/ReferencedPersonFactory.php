<?php

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\PersonScheduleFactory;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServicePerson;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use FKSDB\Models\Persons\ModifiabilityResolver;
use FKSDB\Models\Persons\VisibilityResolver;
use FKSDB\Models\Persons\ReferencedPersonHandlerFactory;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedPersonFactory {
    use SmartObject;

    protected ServicePerson $servicePerson;

    protected PersonFactory $personFactory;

    protected ReferencedPersonHandlerFactory $referencedPersonHandlerFactory;

    protected PersonProvider $personProvider;

    private PersonScheduleFactory $personScheduleFactory;

    private Container $context;

    public function __construct(
        ServicePerson $servicePerson,
        PersonFactory $personFactory,
        ReferencedPersonHandlerFactory $referencedPersonHandlerFactory,
        PersonProvider $personProvider,
        PersonScheduleFactory $personScheduleFactory,
        Container $context
    ) {
        $this->servicePerson = $servicePerson;
        $this->personFactory = $personFactory;
        $this->referencedPersonHandlerFactory = $referencedPersonHandlerFactory;
        $this->personProvider = $personProvider;
        $this->personScheduleFactory = $personScheduleFactory;
        $this->context = $context;
    }

    public function createReferencedPerson(
        array $fieldsDefinition,
        int $acYear,
        string $searchType,
        bool $allowClear,
        ModifiabilityResolver $modifiabilityResolver,
        VisibilityResolver $visibilityResolver,
        ?ModelEvent $event = null
    ): ReferencedId {

        $handler = $this->referencedPersonHandlerFactory->create($acYear, null, $event);
        return new ReferencedId(
            new PersonSearchContainer($this->context, $searchType),
            new ReferencedPersonContainer($this->context, $modifiabilityResolver, $visibilityResolver, $acYear, $fieldsDefinition, $event, $allowClear),
            $this->servicePerson,
            $handler
        );
    }

    final public static function isFilled(ModelPerson $person, string $sub, string $field, int $acYear, ?ModelEvent $event = null): bool {
        $value = self::getPersonValue($person, $sub, $field, $acYear, ReferencedPersonContainer::TARGET_VALIDATION, $event);
        return !($value === null || $value === '');
    }

    /**
     * @param ModelPerson|null $person
     * @param string $sub
     * @param string $field
     * @param int $acYear
     * @param int|null $options
     * @param ModelEvent|null $event
     * @return mixed
     */
    public static function getPersonValue(?ModelPerson $person, string $sub, string $field, int $acYear, ?int $options, ?ModelEvent $event = null) {
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
                return ($flag = $person->getPersonHasFlag($field)) ? (bool)$flag['value'] : null;
            default:
                throw new InvalidArgumentException("Unknown person sub '$sub'.");
        }
    }
}
