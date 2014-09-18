<?php

namespace Persons;

use FKS\Components\Forms\Controls\IReferencedHandler;
use FKS\Components\Forms\Controls\ModelDataConflictException;
use FormUtils;
use ModelException;
use ModelPerson;
use ModelPostContact;
use Nette\ArrayHash;
use Nette\InvalidArgumentException;
use Nette\Object;
use ORM\IModel;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonHistory;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedPersonHandler extends Object implements IReferencedHandler {

    const POST_CONTACT_DELIVERY = 'post_contact_d';
    const POST_CONTACT_PERMANENT = 'post_contact_p';

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var ServicePersonInfo
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
     * @var int
     */
    private $acYear;

    /**
     * @var enum
     */
    private $resolution;

    function __construct(ServicePerson $servicePerson, ServicePersonInfo $servicePersonInfo, ServicePersonHistory $servicePersonHistory, ServiceMPostContact $serviceMPostContact, $acYear, $resolution) {
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->servicePersonHistory = $servicePersonHistory;
        $this->serviceMPostContact = $serviceMPostContact;
        $this->acYear = $acYear;
        $this->resolution = $resolution;
    }

    public function getResolution() {
        return $this->resolution;
    }

    public function setResolution($resolution) {
        $this->resolution = $resolution;
    }

    public function createFromValues(ArrayHash $values) {
        $email = isset($values['person_info']['email']) ? $values['person_info']['email'] : null;
        $person = $this->servicePerson->findByEmail($email);
        if (!$person) {
            $person = $this->servicePerson->createNew();
        }
        $this->store($person, $values);
        return $person;
    }

    public function update(IModel $model, ArrayHash $values) {
        $this->store($model, $values);
    }

    private function store(ModelPerson &$person, ArrayHash $data) {
        /*
         * Process data
         */
        try {
            $this->beginTransaction();

            /*
             * Person & its extensions
             */

            $models = array(
                'person' => &$person,
                'person_info' => ($info = $person->getInfo()) ? : $this->servicePersonInfo->createNew(),
                'person_history' => ($history = $person->getHistory($this->acYear)) ? : $this->servicePersonHistory->createNew(array('ac_year' => $this->acYear)),
                self::POST_CONTACT_DELIVERY => ($dataPostContact = $person->getDeliveryAddress(true)) ? : $this->serviceMPostContact->createNew(array('type' => ModelPostContact::TYPE_DELIVERY)),
                self::POST_CONTACT_PERMANENT => ($dataPostContact = $person->getPermanentAddress(true)) ? : $this->serviceMPostContact->createNew(array('type' => ModelPostContact::TYPE_PERMANENT))
            );
            $services = array(
                'person' => $this->servicePerson,
                'person_info' => $this->servicePersonInfo,
                'person_history' => $this->servicePersonHistory,
                self::POST_CONTACT_DELIVERY => $this->serviceMPostContact,
                self::POST_CONTACT_PERMANENT => $this->serviceMPostContact,
            );

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
                    continue;
                }
                $data[$t]['person_id'] = $models ['person']->person_id; // this works even for person itself
                $services[$t]->updateModel($model, $data[$t]);
                $services[$t]->save($model);
            }

            $this->commit();
        } catch (ModelDataConflictException $e) {
            $this->rollback();
            throw $e;
        } catch (ModelException $e) {
            $this->rollBack();
            throw $e;
        }
    }

    private $outerTransaction = false;

    private function getConflicts($model, ArrayHash $values) {
        $conflicts = new ArrayHash();
        foreach ($values as $key => $value) {
            if (isset($model[$key])) {
                if ($model[$key] instanceof IModel) {
                    $subconflicts = $this->getConflicts($model[$key], $value);
                    if (count($subconflicts)) {
                        $conflicts[$key] = $subconflicts;
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

    private function preparePostContactModels(&$models) {
        if($models[self::POST_CONTACT_PERMANENT]->isNew()) {
            $data = $models[self::POST_CONTACT_DELIVERY]->toArray();
            unset($data['post_contact_id']);
            unset($data['address_id']);
            unset($data['type']);
            $this->serviceMPostContact->updateModel($models[self::POST_CONTACT_PERMANENT], $data);
        }
    }
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

    public function isSecondaryKey($field) {
        return $field == 'person_info.email';
    }

    public function findBySecondaryKey($field, $key) {
        if (!$this->isSecondaryKey($field)) {
            throw new InvalidArgumentException("'$field' is not a secondary key.");
        }
        return $this->servicePerson->findByEmail($key);
    }

}

