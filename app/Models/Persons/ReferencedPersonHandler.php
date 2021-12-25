<?php

namespace FKSDB\Models\Persons;

use FKSDB\Components\EntityForms\PersonFormComponent;
use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\Components\Forms\Controls\Schedule\FullCapacityException;
use FKSDB\Components\Forms\Controls\Schedule\Handler;
use FKSDB\Models\ORM\Models\ModelContestYear;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelPersonHistory;
use FKSDB\Models\ORM\Models\ModelPersonInfo;
use FKSDB\Models\ORM\Models\ModelPostContact;
use FKSDB\Models\ORM\Services\ServiceAddress;
use FKSDB\Models\ORM\Services\ServiceFlag;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\ORM\Services\ServicePersonHasFlag;
use FKSDB\Models\ORM\Services\ServicePersonHistory;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use FKSDB\Models\ORM\Services\ServicePostContact;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Utils\FormUtils;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class ReferencedPersonHandler implements ReferencedHandler
{

    use SmartObject;

    public const POST_CONTACT_DELIVERY = 'post_contact_d';
    public const POST_CONTACT_PERMANENT = 'post_contact_p';

    private ServicePerson $servicePerson;
    private ServicePersonInfo $servicePersonInfo;
    private ServicePersonHistory $servicePersonHistory;

    private ServiceAddress $serviceAddress;
    private ServicePostContact $servicePostContact;

    private ServicePersonHasFlag $servicePersonHasFlag;
    private ModelContestYear $contestYear;
    private Handler $eventScheduleHandler;
    private ServiceFlag $serviceFlag;

    private ModelEvent $event;

    private string $resolution;

    public function __construct(
        ModelContestYear $contestYear,
        string $resolution
    ) {
        $this->contestYear = $contestYear;
        $this->resolution = $resolution;
    }

    public function inject(
        Handler $eventScheduleHandler,
        ServicePerson $servicePerson,
        ServicePersonInfo $servicePersonInfo,
        ServicePersonHistory $servicePersonHistory,
        ServicePersonHasFlag $servicePersonHasFlag,
        ServiceAddress $serviceAddress,
        ServicePostContact $servicePostContact,
        ServiceFlag $serviceFlag
    ): void {
        $this->eventScheduleHandler = $eventScheduleHandler;
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->servicePersonHistory = $servicePersonHistory;
        $this->servicePersonHasFlag = $servicePersonHasFlag;
        $this->serviceAddress = $serviceAddress;
        $this->servicePostContact = $servicePostContact;
        $this->serviceFlag = $serviceFlag;
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
    public function createFromValues(ArrayHash $values): ModelPerson
    {
        $email = isset($values['person_info']['email']) ? $values['person_info']['email'] : null;
        $person = $this->servicePerson->findByEmail($email);
        $person = $this->storePerson($person, (array)$values);
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
    public function update(ActiveRow $model, ArrayHash $values): void
    {
        /** @var ModelPerson $model */
        $this->store($model, $values);
    }

    public function setEvent(ModelEvent $event): void
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
    private function store(ModelPerson &$person, ArrayHash $data): void
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
                self::POST_CONTACT_DELIVERY => $person->getDeliveryPostContact(),
                self::POST_CONTACT_PERMANENT => $person->getPermanentPostContact(true),
            ];
            $originalModels = \array_keys(iterator_to_array($data));

            $this->prepareFlagModels($person, $data, $models);

            $this->preparePostContactModels($models);
            $this->resolvePostContacts($data);

            $data = FormUtils::emptyStrToNull($data);
            $data = FormUtils::removeEmptyHashes($data);
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
            /** @var ModelPostContact|ModelPerson|AbstractModel|ModelPersonInfo|ModelPersonHistory $model */
            foreach ($models as $t => $model) {
                if (!isset($data[$t])) {
                    if (
                        \in_array($t, $originalModels) && \in_array(
                            $t,
                            [self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT]
                        )
                    ) {
                        // delete only post contacts, other "children" could be left all-nulls

                        if ($model) {
                            /** @var ModelPostContact $model */
                            $this->servicePostContact->dispose($model);
                            $this->serviceAddress->dispose($model->getAddress());
                        }
                    }
                    continue;
                }
                switch ($t) {
                    case 'person':
                        $this->storePerson($model, (array)$data);
                        continue 2;
                    case 'person_info':
                        $this->servicePersonInfo->storeModel(
                            array_merge((array)$data['person_info'], ['person_id' => $person->person_id]),
                            $model
                        );
                        continue 2;
                    case 'person_history':
                        $this->servicePersonHistory->storeModel(
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
                            $flag = $this->serviceFlag->findByFid($flagId);
                            $this->servicePersonHasFlag->storeModel([
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

    private function storePostContact(ModelPerson $person, ?ModelPostContact $model, array $data, string $type): void
    {
        if ($model) {
            $this->serviceAddress->updateModel($model->getAddress(), $data);
            $this->servicePostContact->updateModel($model, $data);
        } else {
            $data = array_merge($data, [
                'person_id' => $person->person_id,
                'type' => PersonFormComponent::mapAddressContainerNameToType($type),
            ]);
            $mainModel = $this->serviceAddress->createNewModel($data);
            $data['address_id'] = $mainModel->address_id;
            $this->servicePostContact->createNewModel($data);
        }
    }

    private bool $outerTransaction = false;

    private function getConflicts(array $models, ArrayHash $values): array
    {
        $conflicts = [];
        foreach ($values as $key => $value) {
            if ($key === 'person_has_flag') {
                continue;
            }
            if (isset($models[$key])) {
                if ($models[$key] instanceof ActiveRow) {
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

    private function getModelConflicts(ActiveRow $model, array $values): array
    {
        $conflicts = [];
        foreach ($values as $key => $value) {
            if (isset($model[$key]) && !is_null($model[$key]) && $model[$key] != $value) {
                $conflicts[$key] = $value;
            }
        }
        return $conflicts;
    }

    private function storePerson(?ModelPerson $person, array $data): ModelPerson
    {
        return $this->servicePerson->storeModel((array)$data['person'], $person);
    }

    private function removeConflicts(iterable $data, iterable $conflicts): iterable
    {
        $result = $data;
        foreach ($conflicts as $key => $value) {
            if (isset($data[$key])) {
                if ($data[$key] instanceof ArrayHash) {
                    $result[$key] = $this->removeConflicts($data[$key], $value);
                } else {
                    unset($data[$key]);
                }
            }
        }
        return $result;
    }

    /**
     * @param ModelPostContact[] $models
     */
    private function preparePostContactModels(array &$models): void
    {
        if (!$models[self::POST_CONTACT_PERMANENT] && $models[self::POST_CONTACT_DELIVERY]) {
            $data = array_merge(
                $models[self::POST_CONTACT_DELIVERY]->toArray(),
                $models[self::POST_CONTACT_DELIVERY]->getAddress()->toArray()
            );

            unset($data['post_contact_id']);
            unset($data['address_id']);
            unset($data['type']);

            $addressModel = $this->serviceAddress->createNewModel($data);
            $data['address_id'] = $addressModel->address_id;
            $joinedModel = $this->servicePostContact->createNewModel($data);
            $models[self::POST_CONTACT_PERMANENT] = $joinedModel;
        }
    }

    private function resolvePostContacts(ArrayHash $data): void
    {
        foreach ([self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT] as $type) {
            if (!isset($data[$type])) {
                continue;
            }
            $cleared = FormUtils::removeEmptyHashes(FormUtils::emptyStrToNull($data[$type]), true);
            if (!isset($cleared['address'])) {
                unset($data[$type]);
                continue;
            }
            $data[$type] = $data[$type]['address']; // flatten
            switch ($type) {
                case self::POST_CONTACT_DELIVERY:
                    $data[$type]['type'] = ModelPostContact::TYPE_DELIVERY;
                    break;
                case self::POST_CONTACT_PERMANENT:
                    $data[$type]['type'] = ModelPostContact::TYPE_PERMANENT;
                    break;
            }
        }
    }

    /**
     * @throws ModelException
     */
    private function prepareFlagModels(ModelPerson $person, ArrayHash &$data, array &$models): void
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
            $models['person_has_flag'][$fid] = $person->getPersonHasFlag($fid);
        }
    }

    private function beginTransaction(): void
    {
        $connection = $this->servicePerson->explorer->getConnection();
        if (!$connection->getPdo()->inTransaction()) {
            $connection->beginTransaction();
        } else {
            $this->outerTransaction = true;
        }
    }

    private function commit(): void
    {
        $connection = $this->servicePerson->explorer->getConnection();
        if (!$this->outerTransaction) {
            $connection->commit();
        }
    }

    private function rollback(): void
    {
        $connection = $this->servicePerson->explorer->getConnection();
        if (!$this->outerTransaction) {
            $connection->rollBack();
        }
        //else: TODO ? throw an exception?
    }

    public function isSecondaryKey(string $field): bool
    {
        return $field == 'person_info.email';
    }

    /**
     * @param mixed $key
     * @return ModelPerson|null|ActiveRow
     */
    public function findBySecondaryKey(string $field, string $key): ?ModelPerson
    {
        if (!$this->isSecondaryKey($field)) {
            throw new InvalidArgumentException("'$field' is not a secondary key.");
        }
        return $this->servicePerson->findByEmail($key);
    }
}
