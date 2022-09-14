<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Components\EntityForms\PersonFormComponent;
use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\Components\Forms\Controls\Schedule\FullCapacityException;
use FKSDB\Components\Forms\Controls\Schedule\Handler;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PostContactType;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\ORM\Models\PostContactModel;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\FlagService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\PersonHasFlagService;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Models\ORM\Services\PostContactService;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Utils\FormUtils;
use Nette\SmartObject;

class ReferencedPersonHandler implements ReferencedHandler
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

    private string $resolution;

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

    public function getResolution(): string
    {
        return $this->resolution;
    }

    public function setResolution(string $resolution): void
    {
        $this->resolution = $resolution;
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
        $person = $this->findBySecondaryKey($values['person_info']['email'] ?? null);
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
                'person_schedule' => ((
                    isset($this->event)
                    && isset($data['person_schedule'])
                    && $person->getSerializedSchedule(
                        $this->event,
                        \array_keys((array)$data['person_schedule'])[0]
                    )) ?: null),
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
            $conflicts = $this->getConflicts($models, $data);
            if ($this->resolution === self::RESOLUTION_EXCEPTION) {
                if (count($conflicts)) {
                    throw new ModelDataConflictException($conflicts);
                }
            } elseif ($this->resolution === self::RESOLUTION_KEEP) {
                $data = $this->removeConflicts($data, $conflicts);
            }
            // It's like this: $this->resolution == self::RESOLUTION_OVERWRITE) {
            //    $data = $conflicts;
            /** @var PostContactModel|PersonModel|Model|PersonInfoModel|PersonHistoryModel $model */
            foreach ($models as $t => $model) {
                if (!isset($data[$t])) {
                    if (
                        in_array($t, $originalModels)
                        && in_array(
                            $t,
                            [self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT]
                        )
                    ) {
                        // delete only post contacts, other "children" could be left all-nulls
                        if ($model) {
                            /** @var PostContactModel $model */
                            $this->addressService->disposeModel($model->address);
                            $this->postContactService->disposeModel($model);
                        }
                    }
                    continue;
                }
                switch ($t) {
                    case 'person':
                        $this->storePerson($model, (array)$data);
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
                    case 'person_schedule':
                        $this->eventScheduleHandler->prepareAndUpdate($data[$t], $models['person'], $this->event);
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
            $this->commit();
        } catch (ModelDataConflictException | StorageException | ModelException$exception) {
            $this->rollback();
            throw $exception;
        }
    }

    private function storePostContact(PersonModel $person, ?PostContactModel $model, array $data, string $type): void
    {
        if ($model) {
            $this->addressService->storeModel($data, $model->address);
            $this->postContactService->storeModel($data, $model);
        } else {
            $data = array_merge($data, [
                'person_id' => $person->person_id,
                'type' => PersonFormComponent::mapAddressContainerNameToType($type)->value,
            ]);
            $mainModel = $this->addressService->storeModel($data);
            $data['address_id'] = $mainModel->address_id;
            $this->postContactService->storeModel($data);
        }
    }

    private bool $outerTransaction = false;

    private function getConflicts(array $models, array $values): array
    {
        $conflicts = [];
        foreach ($values as $key => $value) {
            if ($key === 'person_has_flag') {
                continue;
            }
            if (isset($models[$key])) {
                if ($models[$key] instanceof Model) {
                    $subConflicts = $this->getModelConflicts($models[$key], (array)$value);
                    if (count($subConflicts)) {
                        $conflicts[$key] = $subConflicts;
                    }
                } elseif (!is_null($models[$key]) && $models[$key] != $value) {
                    $conflicts[$key] = $value;
                }
            }
        }

        return $conflicts;
    }

    private function getModelConflicts(Model $model, array $values): array
    {
        $conflicts = [];
        foreach ($values as $key => $value) {
            if (isset($model[$key]) && !is_null($model[$key]) && $model[$key] != $value) {
                $conflicts[$key] = $value;
            }
        }
        return $conflicts;
    }

    private function storePerson(?PersonModel $person, array $data): PersonModel
    {
        return $this->personService->storeModel((array)$data['person'], $person);
    }

    private function removeConflicts(iterable $data, iterable $conflicts): iterable
    {
        $result = $data;
        foreach ($conflicts as $key => $value) {
            if (isset($data[$key])) {
                if (is_iterable($data[$key])) {
                    $result[$key] = $this->removeConflicts($data[$key], $value);
                } else {
                    unset($data[$key]);
                }
            }
        }
        return $result;
    }

    /**
     * @param PostContactModel[] $models
     */
    private function preparePostContactModels(array &$models): void
    {
        if (!$models[self::POST_CONTACT_PERMANENT] && $models[self::POST_CONTACT_DELIVERY]) {
            $data = array_merge(
                $models[self::POST_CONTACT_DELIVERY]->toArray(),
                $models[self::POST_CONTACT_DELIVERY]->address->toArray()
            );

            unset($data['post_contact_id']);
            unset($data['address_id']);
            unset($data['type']);

            $addressModel = $this->addressService->storeModel($data);
            $data['address_id'] = $addressModel->address_id;
            $joinedModel = $this->postContactService->storeModel($data);
            $models[self::POST_CONTACT_PERMANENT] = $joinedModel;
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
            $data[$type] = $data[$type]['address']; // flatten
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

    public function findBySecondaryKey(string $key): ?PersonModel
    {
        return $this->personService->findByEmail($key);
    }
}
