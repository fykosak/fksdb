<?php

namespace FKSDB\Models\Persons;

use FKSDB\Components\Controls\Entity\PersonFormComponent;
use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\Components\Forms\Controls\Schedule\FullCapacityException;
use FKSDB\Components\Forms\Controls\Schedule\Handler;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelPostContact;
use FKSDB\Models\ORM\Services\ServiceFlag;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\ORM\Services\ServicePersonHasFlag;
use FKSDB\Models\ORM\Services\ServicePersonHistory;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Models\Exceptions\ModelException;
use FKSDB\Models\ORM\ModelsMulti\ModelMPostContact;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;
use FKSDB\Models\ORM\ServicesMulti\ServiceMPostContact;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedPersonHandler implements IReferencedHandler {
    use SmartObject;

    public const POST_CONTACT_DELIVERY = 'post_contact_d';
    public const POST_CONTACT_PERMANENT = 'post_contact_p';

    private ServicePerson $servicePerson;
    private ServicePersonInfo $servicePersonInfo;
    private ServicePersonHistory $servicePersonHistory;
    private ServiceMPostContact $serviceMPostContact;
    private ServicePersonHasFlag $servicePersonHasFlag;
    private int $acYear;
    private Handler $eventScheduleHandler;
    private ServiceFlag $serviceFlag;

    /** @var ModelEvent */
    private $event;

    /** @var string */
    private $resolution;

    /**
     * ReferencedPersonHandler constructor.
     * @param ServicePerson $servicePerson
     * @param ServicePersonInfo $servicePersonInfo
     * @param ServicePersonHistory $servicePersonHistory
     * @param ServiceMPostContact $serviceMPostContact
     * @param ServicePersonHasFlag $servicePersonHasFlag
     * @param ServiceFlag $serviceFlag
     * @param Handler $eventScheduleHandler
     * @param int $acYear
     * @param string $resolution
     */
    public function __construct(
        ServicePerson $servicePerson,
        ServicePersonInfo $servicePersonInfo,
        ServicePersonHistory $servicePersonHistory,
        ServiceMPostContact $serviceMPostContact,
        ServicePersonHasFlag $servicePersonHasFlag,
        ServiceFlag $serviceFlag,
        Handler $eventScheduleHandler,
        int $acYear,
        $resolution
    ) {
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->servicePersonHistory = $servicePersonHistory;
        $this->serviceMPostContact = $serviceMPostContact;
        $this->servicePersonHasFlag = $servicePersonHasFlag;
        $this->serviceFlag = $serviceFlag;
        $this->acYear = $acYear;
        $this->resolution = $resolution;
        $this->eventScheduleHandler = $eventScheduleHandler;
    }

    public function getResolution(): string {
        return $this->resolution;
    }

    public function setResolution(string $resolution): void {
        $this->resolution = $resolution;
    }

    /**
     * @param ArrayHash $values
     * @return ModelPerson
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     * @throws ModelDataConflictException
     * @throws ModelException
     * @throws NotImplementedException
     * @throws StorageException
     */
    public function createFromValues(ArrayHash $values): ModelPerson {
        $email = isset($values['person_info']['email']) ? $values['person_info']['email'] : null;
        $person = $this->servicePerson->findByEmail($email);
        $person = $this->storePerson($person, (array)$values);
        $this->store($person, $values);
        return $person;
    }

    /**
     * @param IModel $model
     * @param ArrayHash $values
     * @return void
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     * @throws ModelDataConflictException
     * @throws ModelException
     * @throws NotImplementedException
     * @throws StorageException
     */
    public function update(IModel $model, ArrayHash $values): void {
        /** @var ModelPerson $model */
        $this->store($model, $values);
    }

    public function setEvent(ModelEvent $event): void {
        $this->event = $event;
    }

    /**
     * @param ModelPerson $person
     * @param ArrayHash $data
     * @return void
     * @throws ModelException
     * @throws ModelDataConflictException
     * @throws ExistingPaymentException
     * @throws StorageException
     * @throws FullCapacityException
     * @throws NotImplementedException
     */
    private function store(ModelPerson &$person, ArrayHash $data): void {
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
                'person_history' => $person->getHistory($this->acYear),
                'person_schedule' => (($this->event && isset($data['person_schedule']) && $person->getSerializedSchedule($this->event->event_id, \array_keys((array)$data['person_schedule'])[0])) ?: null),
                self::POST_CONTACT_DELIVERY => $person->getDeliveryAddress(),
                self::POST_CONTACT_PERMANENT => $person->getPermanentAddress(true),
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
            foreach ($models as $t => $model) {
                if (!isset($data[$t])) {
                    if (\in_array($t, $originalModels) && \in_array($t, [self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT])) {
                        // delete only post contacts, other "children" could be left all-nulls
                        if ($model) {
                            $this->serviceMPostContact->dispose($model);
                        }
                    }
                    continue;
                }
                switch ($t) {
                    case 'person':
                        $this->storePerson($model, (array)$data);
                        continue 2;
                    case 'person_info':
                        $this->servicePersonInfo->store($person, $model, (array)$data['person_info']);
                        continue 2;
                    case 'person_history':
                        $this->servicePersonHistory->store($person, $model, (array)$data['person_history'], $this->acYear);
                        continue 2;
                    case 'person_schedule':
                        $this->eventScheduleHandler->prepareAndUpdate($data[$t], $models['person'], $this->event);
                        continue 2;
                    case self::POST_CONTACT_PERMANENT:
                    case self::POST_CONTACT_DELIVERY:
                        $this->storePostContact($person, $models[$t], (array)$data[$t], $t);
                        continue 2;
                    case 'person_has_flag':
                        foreach ($data[$t] as $flagId => $flagValue) {
                            $flag = $this->serviceFlag->findByFid($flagId);
                            $flagData = [
                                'value' => $flagValue,
                                'flag_id' => $flag->flag_id,
                            ];
                            if ($models[$t][$flagId]) {
                                $this->servicePersonHasFlag->updateModel2($models[$t][$flagId], (array)$flagData);
                            } else {
                                $flagData['person_id'] = $person->person_id;
                                $this->servicePersonHasFlag->createNewModel((array)$flagData);
                            }
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

    private function storePostContact(ModelPerson $person, ?ModelMPostContact $model, array $data, string $type): void {
        if ($model) {
            $this->serviceMPostContact->updateModel2($model, $data);
        } else {
            $this->serviceMPostContact->createNewModel(array_merge((array)$data, [
                'person_id' => $person->person_id,
                'type' => PersonFormComponent::mapAddressContainerNameToType($type),
            ]));
        }
    }

    private bool $outerTransaction = false;

    /**
     * @param mixed $models
     * @param ArrayHash $values
     * @return array
     */
    private function getConflicts($models, ArrayHash $values): array {
        $conflicts = [];
        foreach ($values as $key => $value) {
            if ($key === 'person_has_flag') {
                continue;
            }
            if (isset($models[$key])) {
                if ($models[$key] instanceof IModel) {
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

    private function getModelConflicts(IModel $model, array $values): array {
        $conflicts = [];
        foreach ($values as $key => $value) {
            if (isset($model[$key]) && !is_null($model[$key]) && $model[$key] != $value) {
                $conflicts[$key] = $value;
            }
        }
        return $conflicts;
    }

    private function storePerson(?ModelPerson $person, array $data): ModelPerson {
        return $this->servicePerson->store($person, (array)$data['person']);
    }

    private function removeConflicts(iterable $data, iterable $conflicts): iterable {
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
     * @param ModelMPostContact[] $models
     */
    private function preparePostContactModels(array &$models): void {
        if (!$models[self::POST_CONTACT_PERMANENT] && $models[self::POST_CONTACT_DELIVERY]) {
            $data = $models[self::POST_CONTACT_DELIVERY]->toArray();
            unset($data['post_contact_id']);
            unset($data['address_id']);
            unset($data['type']);
            $models[self::POST_CONTACT_PERMANENT] = $this->serviceMPostContact->createNewModel(array_merge($data, ['type' => ModelPostContact::TYPE_PERMANENT]));
        }
    }

    private function resolvePostContacts(ArrayHash $data): void {
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
     * @param ModelPerson $person
     * @param ArrayHash $data
     * @param array $models
     * @throws ModelException
     */
    private function prepareFlagModels(ModelPerson $person, ArrayHash &$data, array &$models): void {
        if (!isset($data['person_has_flag'])) {
            return;
        }
        $models['person_has_flag'] = [];
        foreach ($data['person_has_flag'] as $fid => $value) {
            if ($value === null) {
                unset($data['person_has_flag'][$fid]);
                continue;
            }
            $models['person_has_flag'][$fid] = $person->getPersonHasFlag($fid) ?: null;
        }
    }

    private function beginTransaction(): void {
        $connection = $this->servicePerson->getConnection();
        if (!$connection->getPdo()->inTransaction()) {
            $connection->beginTransaction();
        } else {
            $this->outerTransaction = true;
        }
    }

    private function commit(): void {
        $connection = $this->servicePerson->getConnection();
        if (!$this->outerTransaction) {
            $connection->commit();
        }
    }

    private function rollback(): void {
        $connection = $this->servicePerson->getConnection();
        if (!$this->outerTransaction) {
            $connection->rollBack();
        }
        //else: TODO ? throw an exception?
    }

    public function isSecondaryKey(string $field): bool {
        return $field == 'person_info.email';
    }

    /**
     * @param string $field
     * @param mixed $key
     * @return ModelPerson|null|IModel
     */
    public function findBySecondaryKey(string $field, string $key): ?ModelPerson {
        if (!$this->isSecondaryKey($field)) {
            throw new InvalidArgumentException("'$field' is not a secondary key.");
        }
        return $this->servicePerson->findByEmail($key);
    }
}
