<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\Components\Forms\Controls\Schedule\FullCapacityException;
use FKSDB\Components\Forms\Controls\Schedule\Handler;
use FKSDB\Components\Forms\Referenced\Address\AddressHandler;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\FlagService;
use FKSDB\Models\ORM\Services\PersonHasFlagService;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\PostContactService;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

class ReferencedPersonHandler extends ReferencedHandler
{
    use SmartObject;

    public const POST_CONTACT_DELIVERY = 'post_contact_d';
    public const POST_CONTACT_PERMANENT = 'post_contact_p';

    private PersonService $personService;
    private PersonInfoService $personInfoService;
    private PersonHistoryService $personHistoryService;
    private PostContactService $postContactService;

    private PersonHasFlagService $personHasFlagService;
    private ContestYearModel $contestYear;
    private Handler $eventScheduleHandler;
    private FlagService $flagService;
    private Container $container;

    private EventModel $event;

    public function __construct(ContestYearModel $contestYear, ResolutionMode $resolution)
    {
        $this->contestYear = $contestYear;
        $this->resolution = $resolution;
    }

    public function inject(
        Container $container,
        Handler $eventScheduleHandler,
        PersonService $personService,
        PersonInfoService $personInfoService,
        PersonHistoryService $personHistoryService,
        PersonHasFlagService $personHasFlagService,
        PostContactService $postContactService,
        FlagService $flagService
    ): void {
        $this->container = $container;
        $this->eventScheduleHandler = $eventScheduleHandler;
        $this->personService = $personService;
        $this->personInfoService = $personInfoService;
        $this->personHistoryService = $personHistoryService;
        $this->personHasFlagService = $personHasFlagService;
        $this->postContactService = $postContactService;
        $this->flagService = $flagService;
    }

    /**
     * @param PersonModel|null $model
     * @throws NotImplementedException
     */
    public function store(array $values, ?Model $model = null): PersonModel
    {
        if (isset($model)) {
            $this->innerStore($model, $values);
            return $model;
        } else {
            $person = $this->personService->findByEmail($values['person_info']['email'] ?? null);
            $person = $this->storePerson($person, (array)$values['person']);
            $this->innerStore($person, $values);
            return $person;
        }
    }

    public function setEvent(EventModel $event): void
    {
        $this->event = $event;
    }

    /**
     * @throws ModelException
     * @throws ModelDataConflictException
     * @throws ExistingPaymentException
     * @throws StorageException
     * @throws FullCapacityException
     * @throws NotImplementedException
     */
    private function innerStore(PersonModel $person, array $data): void
    {
        $connection = $this->personService->explorer->getConnection();
        try {
            $outerTransaction = false;

            if (!$connection->getPdo()->inTransaction()) {
                $connection->beginTransaction();
            } else {
                $outerTransaction = true;
            }
            $data = FormUtils::removeEmptyValues(FormUtils::emptyStrToNull2($data));

            if (isset($data['person'])) {
                $this->storePerson($person, (array)$data['person']);
            }

            foreach ([self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT] as $key) {
                if (isset($data[$key])) {
                    $type = self::mapAddressContainerNameToType($key);
                    $this->storePostContact(
                        $person,
                        (array)$data[$key],
                        $type,
                        $key
                    );
                }
            }

            if (isset($data['person_info'])) {
                $this->storePersonInfo($person, (array)$data['person_info']);
            }

            if (isset($data['person_history'])) {
                $this->storePersonHistory($person, (array)$data['person_history']);
            }
            if (isset($data['person_has_flag'])) {
                $this->storeFlags($person, (array)$data['person_has_flag']);
            }

            if (isset($data['person_schedule'])) {
                $this->eventScheduleHandler->prepareAndUpdate($data['person_schedule'], $person, $this->event);
            }
            if (!$outerTransaction) {
                $connection->commit();
            }
        } catch (ModelDataConflictException|StorageException|ModelException $exception) {
            if (!$outerTransaction) {
                $connection->rollBack();
            }
            throw $exception;
        }
    }

    private function storePersonInfo(PersonModel $person, array $infoData): void
    {
        $info = $person->getInfo();
        $this->personInfoService->storeModel(
            array_merge(
                $info ? $this->findModelConflicts($info, $infoData, 'person_info') : $infoData,
                ['person_id' => $person->person_id]
            ),
            $info
        );
    }

    private function storePersonHistory(PersonModel $person, array $historyData): void
    {
        $history = $person->getHistoryByContestYear($this->contestYear);
        $this->personHistoryService->storeModel(
            array_merge(
                $history ? $this->findModelConflicts($history, $historyData, 'person_history') : $historyData,
                [
                    'ac_year' => $this->contestYear->ac_year,
                    'person_id' => $person->person_id,
                ]
            ),
            $history
        );
    }

    private function storeFlags(PersonModel $person, array $flagData): void
    {
        foreach ($flagData as $flagId => $flagValue) {
            if (isset($flagValue)) {
                $flag = $this->flagService->findByFid($flagId);
                $personFlag = $person->hasPersonFlag($flagId);
                $this->personHasFlagService->storeModel([
                    'value' => $flagValue,
                    'flag_id' => $flag->flag_id,
                    'person_id' => $person->person_id,
                ], $personFlag);
            }
        }
    }

    private function storePostContact(
        PersonModel $person,
        array $data,
        PostContactType $type,
        string $key
    ): void {
        $model = $person->getPostContact($type);
        if ($model) {
            $data = $this->findModelConflicts($model, $data, $key);
        }
        $handler = new AddressHandler($this->container);
        $address = $handler->store($data['address'], $model ? $model->address : null);
        if ($model) {
            $this->postContactService->storeModel($data, $model);
        } else {
            $data['address_id'] = $address->address_id;
            $data['person_id'] = $person->person_id;
            $data['type'] = $type->value;
            $this->postContactService->storeModel($data);
        }
    }

    public static function mapAddressContainerNameToType(string $containerName): PostContactType
    {
        switch ($containerName) {
            case self::POST_CONTACT_PERMANENT:
                return PostContactType::tryFrom(PostContactType::PERMANENT);
            case self::POST_CONTACT_DELIVERY:
                return PostContactType::tryFrom(PostContactType::DELIVERY);
            default:
                throw new InvalidArgumentException();
        }
    }

    private function storePerson(?PersonModel $person, array $personData): PersonModel
    {
        return $this->personService->storeModel(
            $person ? $this->findModelConflicts($person, $personData, 'person') : $personData,
            $person
        );
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
        ?EventModel $event = null
    ) {
        if (!$person) {
            return null;
        }
        switch ($sub) {
            case 'person_schedule':
                return $person->getSerializedSchedule($event, $field);
            case 'person':
                return $person->{$field};
            case 'person_info':
                $result = ($info = $person->getInfo()) ? $info->{$field} : null;
                if ($field == 'agreed') {
                    // See isFilled() semantics. We consider those who didn't agree as NOT filled.
                    $result = $result ? true : null;
                }
                return $result;
            case 'person_history':
                return ($history = $person->getHistoryByContestYear($contestYear, $extrapolate)) ? $history->{$field}
                    : null;
            case 'post_contact_d':
                return $person->getPostContact(PostContactType::tryFrom(PostContactType::DELIVERY));
            case 'post_contact_p':
                return $person->getPostContact(PostContactType::tryFrom(PostContactType::PERMANENT));
            case 'person_has_flag':
                return ($flag = $person->hasPersonFlag($field)) ? (bool)$flag['value'] : null;
            default:
                throw new \InvalidArgumentException("Unknown person sub '$sub'.");
        }
    }
}
