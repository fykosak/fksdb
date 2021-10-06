<?php

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\PersonScheduleFactory;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServicePerson;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use FKSDB\Models\Persons\ModifiabilityResolver;
use FKSDB\Models\Persons\VisibilityResolver;
use FKSDB\Models\Persons\ReferencedPersonHandlerFactory;

class ReferencedPersonFactory {

    use SmartObject;

    private ServicePerson $servicePerson;

    private PersonFactory $personFactory;

    private ReferencedPersonHandlerFactory $referencedPersonHandlerFactory;

    private PersonProvider $personProvider;

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
        ModelContestYear $contestYear,
        string $searchType,
        bool $allowClear,
        ModifiabilityResolver $modifiabilityResolver,
        VisibilityResolver $visibilityResolver,
        ?ModelEvent $event = null
    ): ReferencedId {
        $handler = $this->referencedPersonHandlerFactory->create($contestYear, null, $event);
        return new ReferencedId(
            new PersonSearchContainer($this->context, $searchType),
            new ReferencedPersonContainer($this->context, $modifiabilityResolver, $visibilityResolver, $contestYear, $fieldsDefinition, $event, $allowClear),
            $this->servicePerson,
            $handler
        );
    }

    final public static function isFilled(ModelPerson $person, string $sub, string $field, ModelContestYear $contestYear, ?ModelEvent $event = null): bool {
        $value = self::getPersonValue($person, $sub, $field, $contestYear, false, false, true, $event);
        return !($value === null || $value === '');
    }

    /**
     * @return mixed
     */
    public static function getPersonValue(?ModelPerson $person, string $sub, string $field, ModelContestYear $contestYear, bool $extrapolate = false, bool $hasDelivery = false, bool $targetValidation = false, ?ModelEvent $event = null) {
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
                return ($history = $person->getHistoryByContestYear($contestYear, $extrapolate)) ? $history[$field] : null;
            case 'post_contact_d':
                return $person->getDeliveryPostContact();
            case 'post_contact_p':
                if ($targetValidation || !$hasDelivery) {
                    return $person->getPermanentPostContact();
                }
                return $person->getPermanentPostContact(true);
            case 'person_has_flag':
                return ($flag = $person->getPersonHasFlag($field)) ? (bool)$flag['value'] : null;
            default:
                throw new InvalidArgumentException("Unknown person sub '$sub'.");
        }
    }
}
