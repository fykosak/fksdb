<?php

namespace Persons;

use FKS\Components\Forms\Controls\AlreadyExistsException;
use FKS\Components\Forms\Controls\IReferencedHandler;
use FormUtils;
use ModelException;
use ModelPerson;
use Nette\ArrayHash;
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

    const RESOLUTION_OVERWRITE = 'overwrite';
    const RESOLUTION_KEEP = 'keep';
    const RESOLUTION_EXCEPTION = 'exception';

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

            $subs = array(
                array(
                    'type' => 'person',
                    'model' => $person,
                    'data' => isset($data['person']) ? $data['person'] : new ArrayHash(),
                    'service' => $this->servicePerson,
                ),
                array(
                    'type' => 'person_info',
                    'model' => ($info = $person->getInfo()) ? : $this->servicePersonInfo->createNew(),
                    'data' => isset($data['person_info']) ? $data['person_info'] : new ArrayHash(),
                    'service' => $this->servicePersonInfo,
                ), array(
                    'type' => 'person_history',
                    'model' => ($info = $person->getHistory($this->acYear)) ? : $this->servicePersonHistory->createNew(array('ac_year' => $this->acYear)),
                    'data' => isset($data['person_history']) ? $data['person_history'] : new ArrayHash(),
                    'service' => $this->servicePersonHistory,
                )
            );
            foreach ($subs as $sub) {
                $sub['data'] = FormUtils::emptyStrToNull($sub['data']);
                if (!$this->checkModel($sub['model'], $sub['data'])) {
                    switch ($this->resolution) {
                        case self::RESOLUTION_EXCEPTION:
                            throw new AlreadyExistsException($person);
                        case self::RESOLUTION_OVERWRITE:
                            $sub['service']->updateModel($sub['model'], $sub['data']);
                        // default: RESOLUTION_KEEP
                    }
                } else {
                    $sub['service']->updateModel($sub['model'], $sub['data']);
                }
                $sub['model']->person_id = $person->person_id; // this works even for person itself
                $sub['service']->save($sub['model']);
                if ($sub['type'] == 'person') {
                    $person = $sub['model']; // model (reference) was changed by the service
                }
            }

            /*
             * Post contact
             */
            $type = (isset($data['post_contact']) && isset($data['post_contact']['type'])) ? $data['post_contact']['type'] : null;
            $addressData = (isset($data['post_contact']) && isset($data['post_contact']['address'])) ? $data['post_contact']['address'] : null;
            $updatePostContact = $type && $addressData;
            if ($updatePostContact) {
                foreach ($person->getMPostContacts($type) as $mPostContact) {
                    $this->serviceMPostContact->dispose($mPostContact);
                }

                $dataPostContact = FormUtils::emptyStrToNull($addressData);
                $mPostContact = $this->serviceMPostContact->createNew($dataPostContact);
                $mPostContact->getPostContact()->person_id = $person->person_id;

                $this->serviceMPostContact->save($mPostContact);
            }

            $this->commit();
        } catch (AlreadyExistsException $e) {
            $this->rollback();
            throw $e;
        } catch (ModelException $e) {
            $this->rollBack();
            throw new PersonHandlerException(null, null, $e);
        }
    }

    private $outerTransaction = false;

    private function checkModel(IModel $model, ArrayHash $values) {
        foreach ($values as $key => $value) {
            //TODO check that overwriting null is handled correctly
            if (isset($model[$key]) && $model[$key] != $value) {
                return false;
            }
        }

        return true;
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

}

