<?php

namespace Persons;

use Authentication\AccountManager;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FormUtils;
use Mail\MailTemplateFactory;
use Mail\SendFailedException;
use ModelException;
use ModelPerson;
use Nette\ArrayHash;
use ORM\IModel;
use ServiceLogin;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonHistory;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonHandler2 {

    const RESOLUTION_OVERWRITE = 'overwrite';
    const RESOLUTION_KEEP = 'keep';
    const RESOLUTION_EXCEPTION = 'exception';

    /**
     * @var ServicePerson
     */
    protected $servicePerson;

    /**
     * @var ServicePersonInfo
     */
    protected $servicePersonInfo;

    /**
     * @var ServicePersonHistory
     */
    protected $servicePersonHistory;

    /**
     * @var ServiceMPostContact
     */
    protected $serviceMPostContact;

    /**
     * @var ServiceLogin
     */
    protected $serviceLogin;

    /**
     * @var MailTemplateFactory
     */
    protected $mailTemplateFactory;

    /**
     * @var AccountManager
     */
    protected $accountManager;

    /**
     * @var ModelPerson
     */
    protected $person;

    function __construct(ServicePerson $servicePerson, ServicePersonInfo $servicePersonInfo, ServicePersonHistory $servicePersonHistory, ServiceMPostContact $serviceMPostContact, ServiceLogin $serviceLogin, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager) {
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->servicePersonHistory = $servicePersonHistory;
        $this->serviceMPostContact = $serviceMPostContact;
        $this->serviceLogin = $serviceLogin;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
    }

    public function update(ModelPerson $person, ArrayHash $data, $acYear, $resolution = self::RESOLUTION_EXCEPTION) {
        $this->store($person, $data, $acYear, $resolution);
    }

    public function createFromValues(ArrayHash $data, $acYear, $resolution = self::RESOLUTION_EXCEPTION) {
        $email = isset($data['person_info']['email']) ? $data['person_info']['email'] : null;
        $person = $this->servicePerson->findByEmail($email);
        if (!$person) {
            $person = $this->servicePerson->createNew();
        }
        $this->store($person, $data, $acYear, $resolution);
        return $person;
    }

    private function store(ModelPerson &$person, ArrayHash $data, $acYear, $resolution) {
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
                    'model' => ($info = $person->getHistory($acYear)) ? : $this->servicePersonHistory->createNew(array('ac_year' => $acYear)),
                    'data' => isset($data['person_history']) ? $data['person_history'] : new ArrayHash(),
                    'service' => $this->servicePersonHistory,
                )
            );
            foreach ($subs as $sub) {
                $sub['data'] = FormUtils::emptyStrToNull($sub['data']);
                if (!$this->checkModel($sub['model'], $sub['data'])) {
                    switch ($resolution) {
                        case self::RESOLUTION_EXCEPTION:
                            throw new ResolutionException($person);
                        case self::RESOLUTION_OVERWRITE:
                            $this->servicePerson->updateModel($sub['model'], $sub['data']);
                        // default: RESOLUTION_KEEP
                    }
                } else {
                    $sub['service']->updateModel($sub['model'], $sub['data']);
                }
                $sub['model']->person_id = $person->person_id; // this works even for perso itself
                $sub['service']->save($sub['model']);
                if ($sub['type'] == 'person') {
                    $person = $sub['model']; // model (reference) was changed by the service
                }
            }

            /*
             * Post contact
             */
            $type = isset($data['post_contact']['type']) ? $data['post_contact']['type'] : null;
            $addressData = isset($data['post_contact']['address']) ? $data['post_contact']['address'] : null;
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

            /*
             * Login
             */
            $email = isset($data['person_info']['email']) ? $data['person_info']['email'] : null;
            $loginData = isset($data['person_info'][PersonFactory::CONT_LOGIN]) ? $data['person_info'][PersonFactory::CONT_LOGIN] : array();
            $createLogin = isset($loginData[PersonFactory::EL_CREATE_LOGIN]) ? $loginData[PersonFactory::EL_CREATE_LOGIN] : null; //TODO

            if ($email && !$person->getLogin() && $createLogin) {
                try {
                    $this->accountManager->createLoginWithInvitation($template, $person, $email);
                    //TODO (acount promise to send email only after success)
                    $presenter->flashMessage(_('Zvací e-mail odeslán.'), $presenter::FLASH_INFO);
                } catch (SendFailedException $e) {
                    //TODO (look above)
                    $presenter->flashMessage(_('Zvací e-mail se nepodařilo odeslat.'), $presenter::FLASH_ERROR);
                }
            }

            $this->commit();
        } catch (ResolutionException $e) {
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

class ResolutionException extends PersonHandlerException {

    /**
     * @var ModelPerson
     */
    private $person;

    public function __construct(ModelPerson $person, $message = null, $code = null, $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->person = $person;
    }

    public function getPerson() {
        return $this->person;
    }

}