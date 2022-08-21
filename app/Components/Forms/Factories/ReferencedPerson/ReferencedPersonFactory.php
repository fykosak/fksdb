<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories\ReferencedPerson;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\PersonScheduleFactory;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\PersonService;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use FKSDB\Models\Persons\ModifiabilityResolver;
use FKSDB\Models\Persons\VisibilityResolver;
use FKSDB\Models\Persons\ReferencedPersonHandlerFactory;

class ReferencedPersonFactory
{
    use SmartObject;

    private PersonService $personService;

    private PersonFactory $personFactory;

    private ReferencedPersonHandlerFactory $referencedPersonHandlerFactory;

    private PersonProvider $personProvider;

    private PersonScheduleFactory $personScheduleFactory;

    private Container $context;

    public function __construct(
        PersonService $personService,
        PersonFactory $personFactory,
        ReferencedPersonHandlerFactory $referencedPersonHandlerFactory,
        PersonProvider $personProvider,
        PersonScheduleFactory $personScheduleFactory,
        Container $context
    ) {
        $this->personService = $personService;
        $this->personFactory = $personFactory;
        $this->referencedPersonHandlerFactory = $referencedPersonHandlerFactory;
        $this->personProvider = $personProvider;
        $this->personScheduleFactory = $personScheduleFactory;
        $this->context = $context;
    }

    public function createReferencedPerson(
        array $fieldsDefinition,
        ContestYearModel $contestYear,
        string $searchType,
        bool $allowClear,
        ModifiabilityResolver $modifiabilityResolver,
        VisibilityResolver $visibilityResolver,
        ?EventModel $event = null
    ): ReferencedId {
        $handler = $this->referencedPersonHandlerFactory->create($contestYear, null, $event);
        return new ReferencedId(
            new PersonSearchContainer($this->context, $searchType),
            new ReferencedPersonContainer(
                $this->context,
                $modifiabilityResolver,
                $visibilityResolver,
                $contestYear,
                $fieldsDefinition,
                $event,
                $allowClear
            ),
            $this->personService,
            $handler
        );
    }

    final public static function isFilled(
        PersonModel $person,
        string $sub,
        string $field,
        ContestYearModel $contestYear,
        ?EventModel $event = null
    ): bool {
        $value = self::getPersonValue($person, $sub, $field, $contestYear, false, false, true, $event);
        return !($value === null || $value === '');
    }

    /**
     * @return mixed
     */
    public static function getPersonValue(
        ?PersonModel $person,
        string $sub,
        string $field,
        ContestYearModel $contestYear,
        bool $extrapolate = false,
        bool $hasDelivery = false,
        bool $targetValidation = false,
        ?EventModel $event = null
    ) {
        if (!$person) {
            return null;
        }
        switch ($sub) {
            case 'person_schedule':
                return $person->getSerializedSchedule($event, $field);
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
                return ($history = $person->getHistoryByContestYear($contestYear, $extrapolate)) ? $history[$field]
                    : null;
            case 'post_contact_d':
                return $person->getPostContact(PostContactType::tryFrom(PostContactType::DELIVERY));
            case 'post_contact_p':
                if ($targetValidation || !$hasDelivery) {
                    return $person->getPermanentPostContact();
                }
                return $person->getPermanentPostContact(false);
            case 'person_has_flag':
                return ($flag = $person->hasPersonFlag($field)) ? (bool)$flag['value'] : null;
            default:
                throw new InvalidArgumentException("Unknown person sub '$sub'.");
        }
    }
}
