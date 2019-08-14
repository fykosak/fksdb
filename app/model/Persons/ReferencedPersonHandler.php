<?php

namespace Persons;

use FKSDB\Components\Forms\Controls\IReferencedHandler;
use FKSDB\Components\Forms\Controls\ModelDataConflictException;
use FKSDB\Components\Forms\Controls\PersonAccommodation\ExistingPaymentException;
use FKSDB\Components\Forms\Controls\PersonAccommodation\Handler;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Services\ServiceEventPersonAccommodation;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServicePersonHistory;
use FKSDB\ORM\Services\ServicePersonInfo;
use FKSDB\Submits\StorageException;
use FormUtils;
use ModelException;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;
use Nette\Utils\JsonException;
use ServiceMPersonHasFlag;
use ServiceMPostContact;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedPersonHandler implements IReferencedHandler {
    use SmartObject;
    const POST_CONTACT_DELIVERY = 'post_contact_d';
    const POST_CONTACT_PERMANENT = 'post_contact_p';

    /**
     * @var \FKSDB\ORM\Services\ServicePerson
     */
    private $servicePerson;

    /**
     * @var \FKSDB\ORM\Services\ServicePersonInfo
     */
    private $servicePersonInfo;

    /**
     * @var ServicePersonHistory
     */
    private $servicePersonHistory;

    /**
     * @var ServiceMPostContact
     */
    private $serviceMPostContact;

    /**
     * @var ServiceMPersonHasFlag
     */
    private $serviceMPersonHasFlag;

    /**
     * @var int
     */
    private $acYear;
    /**
     * @var integer
     */
    private $eventId;

    /**
     * @var mixed
     */
    private $resolution;
    /**
     * @var ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;
    /**
     * @var Handler
     */
    private $eventAccommodationHandler;

    /**
     * ReferencedPersonHandler constructor.
     * @param Handler $eventAccommodation
     * @param ServiceEventPersonAccommodation $serviceEventPersonAccommodation
     * @param \FKSDB\ORM\Services\ServicePerson $servicePerson
     * @param \FKSDB\ORM\Services\ServicePersonInfo $servicePersonInfo
     * @param \FKSDB\ORM\Services\ServicePersonHistory $servicePersonHistory
     * @param ServiceMPostContact $serviceMPostContact
     * @param ServiceMPersonHasFlag $serviceMPersonHasFlag
     * @param $acYear
     * @param $resolution
     */
    function __construct(
        Handler $eventAccommodation,
        ServiceEventPersonAccommodation $serviceEventPersonAccommodation,
        ServicePerson $servicePerson,
        ServicePersonInfo $servicePersonInfo,
        ServicePersonHistory $servicePersonHistory,
        ServiceMPostContact $serviceMPostContact,
        ServiceMPersonHasFlag $serviceMPersonHasFlag,
        $acYear,
        $resolution
    ) {
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->servicePersonHistory = $servicePersonHistory;
        $this->serviceMPostContact = $serviceMPostContact;
        $this->serviceMPersonHasFlag = $serviceMPersonHasFlag;
        $this->acYear = $acYear;
        $this->resolution = $resolution;
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
        $this->eventAccommodationHandler = $eventAccommodation;
    }

    /**
     * @return mixed
     */
    public function getResolution() {
        return $this->resolution;
    }

    /**
     * @param $resolution
     * @return mixed|void
     */
    public function setResolution($resolution) {
        $this->resolution = $resolution;
    }

    /**
     * @param ArrayHash $values
     * @return AbstractModelSingle|ModelPerson|null
     * @throws JsonException
     * @throws ExistingPaymentException
     */
    public function createFromValues(ArrayHash $values) {
        $email = isset($values['person_info']['email']) ? $values['person_info']['email'] : null;
        $person = $this->servicePerson->findByEmail($email);
        if (!$person) {
            $person = $this->servicePerson->createNew();
        }
        $this->store($person, $values);
        return $person;
    }

    /**
     * @param \FKSDB\ORM\IModel $model
     * @param ArrayHash $values
     * @throws JsonException
     * @throws ExistingPaymentException
     */
    public function update(IModel $model, ArrayHash $values) {
        /**
         * @var ModelPerson $model
         */
        $this->store($model, $values);
    }

    /**
     * @param $eventId
     */
    public function setEventId($eventId) {
        $this->eventId = $eventId;
    }

    /**
     * @param ModelPerson $person
     * @param ArrayHash $data
     * @throws \FKSDB\Submits\StorageException
     * @throws ModelException
     * @throws ModelDataConflictException
     * @throws JsonException
     * @throws ExistingPaymentException
     * @return void
     */
    private function store(ModelPerson &$person, ArrayHash $data) {
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
                'person_info' => ($info = $person->getInfo()) ?: $this->servicePersonInfo->createNew(),
                'person_history' => ($history = $person->getHistory($this->acYear)) ?: $this->servicePersonHistory->createNew(['ac_year' => $this->acYear]),
                'person_accommodation' => ($personAccommodation = ($this->eventId && $person->getSerializedAccommodationByEventId($this->eventId)) ?: null),
                self::POST_CONTACT_DELIVERY => ($dataPostContact = $person->getDeliveryAddress()) ?: $this->serviceMPostContact->createNew(['type' => ModelPostContact::TYPE_DELIVERY]),
                self::POST_CONTACT_PERMANENT => ($dataPostContact = $person->getPermanentAddress(true)) ?: $this->serviceMPostContact->createNew(['type' => ModelPostContact::TYPE_PERMANENT])
            ];
            $services = [
                'person' => $this->servicePerson,
                'person_info' => $this->servicePersonInfo,
                'person_history' => $this->servicePersonHistory,
                'person_accommodation' => $this->serviceEventPersonAccommodation,
                self::POST_CONTACT_DELIVERY => $this->serviceMPostContact,
                self::POST_CONTACT_PERMANENT => $this->serviceMPostContact,
            ];

            $originalModels = array_keys(iterator_to_array($data));

            $this->prepareFlagServices($data, $services);
            $this->prepareFlagModels($person, $data, $models);

            $this->preparePostContactModels($models);
            $this->resolvePostContacts($data);

            $data = FormUtils::emptyStrToNull($data);
            $data = FormUtils::removeEmptyHashes($data);
            $conflicts = $this->getConflicts($models, $data);

            if ($this->resolution == self::RESOLUTION_EXCEPTION) {
                if (count($conflicts)) {
                    throw new ModelDataConflictException($conflicts);
                }
            } else if ($this->resolution == self::RESOLUTION_KEEP) {
                $data = $this->removeConflicts($data, $conflicts);
            }
            // It's like this: $this->resolution == self::RESOLUTION_OVERWRITE) {
            //    $data = $conflicts;

            foreach ($models as $t => & $model) {
                if (!isset($data[$t])) {
                    if (in_array($t, $originalModels) && in_array($t, array(self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT))) {
                        // delete only post contacts, other "children" could be left all-nulls
                        $services[$t]->dispose($model);
                    }
                    continue;
                }
                if ($t == 'person_accommodation' && isset($data[$t])) {
                    $this->eventAccommodationHandler->prepareAndUpdate($data[$t], $models['person'], $this->eventId);

                    continue;
                }
                $data[$t]['person_id'] = $models['person']->person_id; // this works even for person itself

                $services[$t]->updateModel($model, $data[$t]);
                $services[$t]->save($model);
            }

            $this->commit();
        } catch (ModelDataConflictException $exception) {
            $this->rollback();
            throw $exception;
        } catch (ModelException $exception) {
            $this->rollback();
            throw $exception;
        } catch (StorageException $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    private $outerTransaction = false;

    /**
     * @param $model
     * @param ArrayHash $values
     * @return ArrayHash
     */
    private function getConflicts($model, ArrayHash $values) {
        $conflicts = new ArrayHash();
        foreach ($values as $key => $value) {
            if (isset($model[$key])) {
                if ($model[$key] instanceof IModel) {
                    $subConflicts = $this->getConflicts($model[$key], $value);
                    if (count($subConflicts)) {
                        $conflicts[$key] = $subConflicts;
                    }
                } else {
                    if ($model[$key] != $value) {
                        $conflicts[$key] = $value;
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * @param ArrayHash $data
     * @param ArrayHash $conflicts
     * @return ArrayHash
     */
    private function removeConflicts(ArrayHash $data, ArrayHash $conflicts) {
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
     * @param $models
     */
    private function preparePostContactModels(&$models) {
        if ($models[self::POST_CONTACT_PERMANENT]->isNew()) {
            $data = $models[self::POST_CONTACT_DELIVERY]->toArray();
            unset($data['post_contact_id']);
            unset($data['address_id']);
            unset($data['type']);
            $this->serviceMPostContact->updateModel($models[self::POST_CONTACT_PERMANENT], $data);
        }
    }

    /**
     * @param ArrayHash $data
     */
    private function resolvePostContacts(ArrayHash $data) {
        foreach (array(self::POST_CONTACT_DELIVERY, self::POST_CONTACT_PERMANENT) as $type) {
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
     * @param $models
     */
    private function prepareFlagModels(ModelPerson &$person, ArrayHash &$data, &$models) {
        if (!isset($data['person_has_flag'])) {
            return;
        }

        foreach ($data['person_has_flag'] as $fid => $value) {
            if ($value === null) {
                continue;
            }

            $models[$fid] = ($flag = $person->getMPersonHasFlag($fid)) ?: $this->serviceMPersonHasFlag->createNew(array('fid' => $fid));

            $data[$fid] = new ArrayHash();
            $data[$fid]['value'] = $value;
        }
        unset($data['person_has_flag']);
    }

    /**
     * @param ArrayHash $data
     * @param $services
     */
    private function prepareFlagServices(ArrayHash &$data, &$services) {
        if (!isset($data['person_has_flag'])) {
            return;
        }

        foreach ($data['person_has_flag'] as $fid => $value) {
            $services[$fid] = $this->serviceMPersonHasFlag;
        }
    }

    private function beginTransaction() {
        $connection = $this->servicePerson->getConnection();
        if (!$connection->inTransaction()) {
            $connection->beginTransaction();
        } else {
            $this->outerTransaction = true;
        }
    }

    private function commit() {
        $connection = $this->servicePerson->getConnection();
        if (!$this->outerTransaction) {
            $connection->commit();
        }
    }

    private function rollback() {
        $connection = $this->servicePerson->getConnection();
        if (!$this->outerTransaction) {
            $connection->rollBack();
        }
        //else: TODO ? throw an exception?
    }

    /**
     * @param $field
     * @return bool|mixed
     */
    public function isSecondaryKey($field) {
        return $field == 'person_info.email';
    }

    /**
     * @param string $field
     * @param mixed $key
     * @return \FKSDB\ORM\Models\ModelPerson|null|\FKSDB\ORM\IModel
     */
    public function findBySecondaryKey($field, $key) {
        if (!$this->isSecondaryKey($field)) {
            throw new InvalidArgumentException("'$field' is not a secondary key.");
        }
        return $this->servicePerson->findByEmail($key);
    }

}
