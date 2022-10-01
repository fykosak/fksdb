<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Components\EntityForms\PersonFormComponent;
use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\Components\Forms\Controls\Schedule\FullCapacityException;
use FKSDB\Components\Forms\Controls\Schedule\Handler;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\AddressService;
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
use Nette\SmartObject;

class ReferencedPersonHandler extends ReferencedHandler
{
    use SmartObject;

    public const POST_CONTACT_DELIVERY = 'post_contact_d';
    public const POST_CONTACT_PERMANENT = 'post_contact_p';

    private PersonService $personService;
    private PersonInfoService $personInfoService;
    private PersonHistoryService $personHistoryService;

    private AddressService $addressService;
    private PostContactService $postContactService;

    private PersonHasFlagService $personHasFlagService;
    private ContestYearModel $contestYear;
    private Handler $eventScheduleHandler;
    private FlagService $flagService;

    private EventModel $event;

    public function __construct(
        ContestYearModel $contestYear,
        string $resolution
    ) {
        $this->contestYear = $contestYear;
        $this->resolution = $resolution;
    }

    public function inject(
        Handler $eventScheduleHandler,
        PersonService $personService,
        PersonInfoService $personInfoService,
        PersonHistoryService $personHistoryService,
        PersonHasFlagService $personHasFlagService,
        AddressService $addressService,
        PostContactService $postContactService,
        FlagService $flagService
    ): void {
        $this->eventScheduleHandler = $eventScheduleHandler;
        $this->personService = $personService;
        $this->personInfoService = $personInfoService;
        $this->personHistoryService = $personHistoryService;
        $this->personHasFlagService = $personHasFlagService;
        $this->addressService = $addressService;
        $this->postContactService = $postContactService;
        $this->flagService = $flagService;
    }

    /**
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     * @throws ModelDataConflictException
     * @throws ModelException
     * @throws NotImplementedException
     * @throws StorageException
     */
    public function createFromValues(array $values): PersonModel
    {
        $person = $this->personService->findByEmail($values['person_info']['email'] ?? null);
        $person = $this->storePerson($person, $values);
        $this->store($person, $values);
        return $person;
    }

    /**
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     * @throws ModelDataConflictException
     * @throws ModelException
     * @throws NotImplementedException
     * @throws StorageException
     */
    public function update(Model $model, array $values): void
    {
        /** @var PersonModel $model */
        $this->store($model, $values);
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
    private function store(PersonModel &$person, array $data): void
    {
        /*
         * Process data
         */
        try {
            $this->beginTransaction();
            /*
             * Person & its extensions
             */

            $models = [
                'person' => &$person,
                'person_info' => $person->getInfo(),
                'person_history' => $person->getHistoryByContestYear($this->contestYear),
                self::POST_CONTACT_DELIVERY => $person->getPostContact(
                    PostContactType::tryFrom(PostContactType::DELIVERY)
                ),
                self::POST_CONTACT_PERMANENT => $person->getPermanentPostContact(false),
            ];
            $originalModels = \array_keys($data);

            $this->prepareFlagModels($person, $data, $models);

            $this->preparePostContactModels($models);
            $this->resolvePostContacts($data);

            $data = FormUtils::removeEmptyValues(FormUtils::emptyStrToNull2($data));
            $data = $this->findConflicts($models, $data);
            /** @var PostContactModel|PersonModel|Model|PersonInfoModel|PersonHistoryModel $model */
            foreach ($models as $t => $model) {
                if (!isset($data[$t])) {
                    switch ($t) {
                        case self::POST_CONTACT_DELIVERY:
                        case self::POST_CONTACT_PERMANENT:
                            if (in_array($t, $originalModels) && $model) {
                                // delete only post contacts, other "children" could be left all-nulls
                                /** @var PostContactModel $model */
                                $this->addressService->disposeModel($model->address);
                                $this->postContactService->disposeModel($model);
                            }
                    }
                    continue;
                }
                switch ($t) {
                    case 'person':
                        $this->storePerson($model, $data);
                        continue 2;
                    case 'person_info':
                        $this->personInfoService->storeModel(
                            array_merge((array)$data['person_info'], ['person_id' => $person->person_id]),
                            $model
                        );
                        continue 2;
                    case 'person_history':
                        $this->personHistoryService->storeModel(
                            array_merge((array)$data['person_history'], [
                                'ac_year' => $this->contestYear->ac_year,
                                'person_id' => $person->person_id,
                            ]),
                            $model
                        );
                        continue 2;
                    case self::POST_CONTACT_PERMANENT:
                    case self::POST_CONTACT_DELIVERY:
                        $this->storePostContact($person, $model, (array)$data[$t], $t);
                        continue 2;
                    case 'person_has_flag':
                        foreach ($data[$t] as $flagId => $flagValue) {
                            $flag = $this->flagService->findByFid($flagId);
                            $this->personHasFlagService->storeModel([
                                'value' => $flagValue,
                                'flag_id' => $flag->flag_id,
                                'person_id' => $person->person_id,
                            ], $model[$flagId]);
                        }
                        continue 2;
                }
            }

            if (isset($data['person_schedule'])) {
                $this->eventScheduleHandler->prepareAndUpdate($data['person_schedule'], $person, $this->event);
            }

            $this->commit();
        } catch (ModelDataConflictException | StorageException | ModelException$exception) {
            $this->rollback();
            throw $exception;
        }
    }

    private function storePostContact(
        PersonModel $person,
        ?PostContactModel $model,
        array $data,
        string $type
    ): void {
        if ($model) {
            $this->addressService->storeModel($data['address'], $model->address);
            $this->postContactService->storeModel($data, $model);
        } else {
            $address = $this->addressService->storeModel($data['address']);
            $data['address_id'] = $address->address_id;
            $data['person_id'] = $person->person_id;
            $data['type'] = PersonFormComponent::mapAddressContainerNameToType($type)->value;
            $this->postContactService->storeModel($data);
        }
    }

    private bool $outerTransaction = false;

    private function findConflicts(array $models, array $values): array
    {
        foreach ($values as $key => $value) {
            if ($key === 'person_has_flag') {
                continue;
            }
            if (isset($models[$key])) {
                if ($models[$key] instanceof Model) {
                    $values[$key] = $this->findModelConflicts($models[$key], (array)$value, $key);
                } elseif (isset($models[$key]) && $models[$key] != $value) {
                    switch ($this->resolution) {
                        case self::RESOLUTION_EXCEPTION:
                            throw new ModelDataConflictException([$key => $value]);
                        case self::RESOLUTION_KEEP:
                            unset($values[$key]);
                            break;
                        case self::RESOLUTION_OVERWRITE:
                            break;
                    }
                }
            }
        }
        return $values;
    }

    private function findModelConflicts(Model $model, array $values, string $subKey): array
    {
        foreach ($values as $key => $value) {
            if (isset($model[$key]) && $model[$key] != $value) {
                switch ($this->resolution) {
                    case self::RESOLUTION_EXCEPTION:
                        throw new ModelDataConflictException([$subKey => [$key => $value]]);
                    case self::RESOLUTION_KEEP:
                        unset($values[$key]);
                        break;
                    case self::RESOLUTION_OVERWRITE:
                        break;
                }
            }
        }
        return $values;
    }

    private function storePerson(?PersonModel $person, array $data): PersonModel
    {
        return $this->personService->storeModel((array)$data['person'], $person);
    }

    /**
     * @param PostContactModel[] $models
     */
    private function preparePostContactModels(array &$models): void
    {
        if (!$models[self::POST_CONTACT_PERMANENT] && $models[self::POST_CONTACT_DELIVERY]) {
            $addressData = $models[self::POST_CONTACT_DELIVERY]->address->toArray();
            unset($addressData['address_id']);
            $addressModel = $this->addressService->storeModel($addressData);

            $postContactData = $models[self::POST_CONTACT_DELIVERY]->toArray();
            unset($postContactData['post_contact_id']);
            unset($postContactData['address_id']);
            $postContactData['type'] = PostContactType::PERMANENT;
            $postContactData['address_id'] = $addressModel->address_id;

            $models[self::POST_CONTACT_PERMANENT] = $this->postContactService->storeModel($postContactData);
        }
    }

    private function resolvePostContacts(array &$data): void
    {
        foreach ([self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT] as $type) {
            if (!isset($data[$type])) {
                continue;
            }
            $cleared = FormUtils::removeEmptyValues(FormUtils::emptyStrToNull2($data[$type]), true);
            if (!isset($cleared['address'])) {
                unset($data[$type]);
                continue;
            }
            //$data[$type] = $data[$type]['address']; // flatten
            switch ($type) {
                case self::POST_CONTACT_DELIVERY:
                    $data[$type]['type'] = PostContactType::DELIVERY;
                    break;
                case self::POST_CONTACT_PERMANENT:
                    $data[$type]['type'] = PostContactType::PERMANENT;
                    break;
            }
        }
    }

    /**
     * @throws ModelException
     */
    private function prepareFlagModels(PersonModel $person, array &$data, array &$models): void
    {
        if (!isset($data['person_has_flag'])) {
            return;
        }
        $models['person_has_flag'] = [];
        foreach ($data['person_has_flag'] as $fid => $value) {
            if ($value === null) {
                unset($data['person_has_flag'][$fid]);
                continue;
            }
            $models['person_has_flag'][$fid] = $person->hasPersonFlag($fid);
        }
    }

    private function beginTransaction(): void
    {
        $connection = $this->personService->explorer->getConnection();
        if (!$connection->getPdo()->inTransaction()) {
            $connection->beginTransaction();
        } else {
            $this->outerTransaction = true;
        }
    }

    private function commit(): void
    {
        $connection = $this->personService->explorer->getConnection();
        if (!$this->outerTransaction) {
            $connection->commit();
        }
    }

    private function rollback(): void
    {
        $connection = $this->personService->explorer->getConnection();
        if (!$this->outerTransaction) {
            $connection->rollBack();
        }
        //else: TODO ? throw an exception?
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
                if ($targetValidation || !$hasDelivery) {
                    return $person->getPermanentPostContact();
                }
                return $person->getPermanentPostContact(false);
            case 'person_has_flag':
                return ($flag = $person->hasPersonFlag($field)) ? (bool)$flag['value'] : null;
            default:
                throw new \InvalidArgumentException("Unknown person sub '$sub'.");
        }
    }
}
