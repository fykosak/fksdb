<?php

namespace OrgModule;

use AbstractModelSingle;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use FKSDB\Components\WizardComponent;
use FormUtils;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Kdyby\Extension\Forms\Replicator\Replicator;
use ModelException;
use ModelPerson;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SubmitButton;
use Nette\NotImplementedException;
use Nette\Utils\Html;
use Persons\Deduplication\Merger;
use ServiceLogin;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonPresenter extends EntityPresenter {

    /**
     * @persistent
     * @var string
     */
    public $backlink = '';

    const CONT_PERSON = 'person';
    const CONT_ADDRESSES = 'addresses';
    const CONT_PERSON_INFO = 'personInfo';

    protected $modelResourceId = 'person';

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;

    /**
     * @var ServiceLogin
     */
    private $serviceLogin;

    /**
     * @var ServiceMPostContact
     */
    private $serviceMPostContact;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var UniqueEmailFactory
     */
    private $uniqueEmailFactory;

    /**
     * @var Merger
     */
    private $personMerger;

    /**
     * @var ModelPerson
     */
    private $trunkPerson;

    /**
     * @var ModelPerson
     */
    private $mergedPerson;

    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function injectServicePersonInfo(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function injectServiceLogin(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    public function injectServiceMPostContact(ServiceMPostContact $serviceMPostContact) {
        $this->serviceMPostContact = $serviceMPostContact;
    }

    public function injectAddressFactory(AddressFactory $addressFactory) {
        $this->addressFactory = $addressFactory;
    }

    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    public function injectUniqueEmailFactory(UniqueEmailFactory $uniqueEmailFactory) {
        $this->uniqueEmailFactory = $uniqueEmailFactory;
    }

    public function injectPersonMerger(Merger $personMerger) {
        $this->personMerger = $personMerger;
    }

    public function authorizedMerge($trunkId, $mergedId) {
        $this->trunkPerson = $this->servicePerson->findByPrimary($trunkId);
        $this->mergedPerson = $this->servicePerson->findByPrimary($mergedId);
        if (!$this->trunkPerson || !$this->mergedPerson) {
            throw new BadRequestException('Neexistující osoba.', 404);
        }
        $authorized = $this->getContestAuthorizator()->isAllowed($this->trunkPerson, 'merge', $this->getSelectedContest()) &&
                $this->getContestAuthorizator()->isAllowed($this->mergedPerson, 'merge', $this->getSelectedContest());
        $this->setAuthorized($authorized);
    }

    public function actionMerge($trunkId, $mergedId) {
        $this->personMerger->setMergedPair($this->trunkPerson, $this->mergedPerson);
        $this->updateMergeForm($this['mergeForm']);
    }

    public function titleList() {
        $this->setTitle(_('Osoby'));
    }

    public function titleCreate() {
        $this->setTitle(_('Založit osobu'));
    }

    public function titleEdit($id) {
        $person = $this->getModel();
        $this->setTitle(sprintf(_('Úprava osoby %s'), $person->getFullname()));
    }

    protected function createComponentCreateComponent($name) {
        // So far, there's no use case that creates bare person.
        throw new NotImplementedException();
    }

    protected function createComponentEditComponent($name) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        $person = $this->getModel();

        /*
         * Person
         */
        $group = $form->addGroup(_('Osoba'));
        $personContainer = $this->personFactory->createPerson(PersonFactory::SHOW_DISPLAY_NAME | PersonFactory::SHOW_GENDER, $group);
        $form->addComponent($personContainer, self::CONT_PERSON);

        /**
         * Addresses
         */
        $group = $form->addGroup(_('Adresy'));
        $factory = $this->addressFactory;
        if (count($person->getContestants())) {
            $defaultAddresses = 1;
        } else {
            $defaultAddresses = 0;
        }
        $replicator = new Replicator(function($replContainer) use($factory, $group) {
                    $factory->buildAddress($replContainer, $group);
                    $replContainer->addComponent($factory->createTypeElement(), 'type');

                    $replContainer->addSubmit('remove', _('Odebrat adresu'))->addRemoveOnClick();
                }, $defaultAddresses, true);
        $replicator->containerClass = 'FKSDB\Components\Forms\Containers\AddressContainer';

        $form->addComponent($replicator, self::CONT_ADDRESSES);

        $replicator->addSubmit('add', _('Přidat adresu'))->addCreateOnClick();


        /**
         * Personal information
         */
        $group = $form->addGroup(_('Osobní informace'));
        $login = $person->getLogin();
        $rule = $this->uniqueEmailFactory->create($person);

        $options = PersonFactory::SHOW_EMAIL;
        if (count($person->getOrgs()) > 0) {
            $options |= PersonFactory::SHOW_ORG_INFO;
        }
        $infoContainer = $this->personFactory->createPersonInfo($options, $group, $rule);
        $form->addComponent($infoContainer, self::CONT_PERSON_INFO);

        $form->setCurrentGroup();

        $form->addSubmit('send', _('Uložit'))->onClick[] = array($this, 'handleEditFormSuccess');

        return $form;
    }

    protected function createComponentMergeForm($name) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        $form->addSubmit('send', _('Sloučit osoby'));
        $form->onSuccess[] = array($this, 'handleMergeFormSuccess');
        return $form;
    }

    private function updateMergeForm(Form $form) {
        if (false && !$form->isSubmitted()) { // new form is without any conflict, we use it to clear the session
            $this->setMergeConflicts(null);
            return;
        }

        $conflicts = $this->getMergeConflicts();

        foreach ($conflicts as $table => $pairs) {
            $form->addGroup($table);
            $tableContainer = $form->addContainer($table);

            foreach ($pairs as $pairId => $data) {
                if (!isset($data[Merger::IDX_TRUNK])) {
                    continue;
                }
                $pairContainer = $tableContainer->addContainer($pairId);
                foreach ($data[Merger::IDX_TRUNK] as $column => $value) {
                    if (isset($data[Merger::IDX_RESOLUTION]) && array_key_exists($column, $data[Merger::IDX_RESOLUTION])) {
                        $default = $data[Merger::IDX_RESOLUTION][$column];
                    } else {
                        $default = $value; // default is trunk
                    }
                    $description = Html::el('div');

                    $trunkDesc = Html::el('div');
                    $trunkDesc->setText(_('Trunk') . ': ' . $value);
                    $description->add($trunkDesc);

                    $mergedDesc = Html::el('div');
                    $mergedDesc->setText(_('Merged') . ': ' . $data[Merger::IDX_MERGED][$column]);
                    $description->add($mergedDesc);

                    $pairContainer->addText($column, $column)
                            ->setOption('description', $description)
                            ->setDefaultValue($default);
                }
            }
        }
    }

    protected function setDefaults(AbstractModelSingle $person, Form $form) {
        $defaults = array();

        $defaults[self::CONT_PERSON] = $person;

        $addresses = array();
        foreach ($person->getMPostContacts() as $mPostContact) {
            $addresses[] = $mPostContact;
        }
        $defaults[self::CONT_ADDRESSES] = $addresses;

        $info = $person->getInfo();
        if ($info) {
            $defaults[self::CONT_PERSON_INFO] = $info;
        }

        $login = $person->getLogin();
        if ($login) {
            $defaults[self::CONT_PERSON_INFO]['email'] = $login->email;
        }

        $form->setDefaults($defaults);
    }

    /**
     * @internal
     * @param WizardComponent $wizard
     * @throws ModelException
     */
    public function handleEditFormSuccess(SubmitButton $button) {
        $form = $button->getForm();
        $connection = $this->servicePerson->getConnection();
        $values = $form->getValues();
        $person = $this->getModel();

        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            /*
             * Person
             */
            $dataPerson = FormUtils::emptyStrToNull($values[self::CONT_PERSON]);
            $this->servicePerson->updateModel($person, $dataPerson);
            $this->servicePerson->save($person);



            /*
             * Post contacts
             */
            foreach ($person->getMPostContacts() as $mPostContact) {
                $this->serviceMPostContact->dispose($mPostContact);
            }

            $dataPostContacts = $values[self::CONT_ADDRESSES];
            foreach ($dataPostContacts as $dataPostContact) {
                $dataPostContact = FormUtils::emptyStrToNull((array) $dataPostContact);
                $mPostContact = $this->serviceMPostContact->createNew($dataPostContact);
                $mPostContact->getPostContact()->person_id = $person->person_id;

                $this->serviceMPostContact->save($mPostContact);
            }


            /*
             * Email stored both in login and person_info
             */
            $dataInfo = $values[self::CONT_PERSON_INFO];
            $dataInfo = FormUtils::emptyStrToNull($dataInfo);
            $email = $dataInfo['email'];
            if ($email) {
                unset($dataInfo['email']);
                $login = $person->getLogin();
                if ($login) {
                    $login->email = $email;
                    $this->serviceLogin->save($login);
                } else {
                    $dataInfo['email'] = $email; // we'll store it as personal info
                }
            } else {
                $dataInfo['email'] = null; // erase the person_info field
            }

            /*
             * Personal info
             */
            $personInfo = $person->getInfo();
            if (!$personInfo) {
                $personInfo = $this->servicePersonInfo->createNew($dataInfo);
                $personInfo->person_id = $person->person_id;
            } else {
                unset($dataInfo['agreed']); // not to overwrite existing confirmation
                $this->servicePersonInfo->updateModel($personInfo, $dataInfo);
            }

            $this->servicePersonInfo->save($personInfo);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage(sprintf('Údaje osoby %s upraveny.', $person->getFullname()), self::FLASH_SUCCESS);

            $this->restoreRequest($this->backlink);
            $this->redirect('list');
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage(_('Chyba při úpravě osoby.'), self::FLASH_ERROR);
        }
    }

    public function handleMergeFormSuccess(Form $form) {
        $values = $form->getValues();

        $merger = $this->personMerger;
        $merger->setConflictResolution($values);
        if ($merger->merge()) {
            $this->setMergeConflicts(null); // flush the session
            $this->flashMessage(_('Osoby úspešně sloučeny.'), self::FLASH_SUCCESS);
            $this->redirect('this'); //TODO backlink redirect
        } else {
            $this->setMergeConflicts($merger->getConflicts());
            $this->flashMessage(_('Je třeba ručně vyřešit konflikty.'), self::FLASH_INFO);
            $this->redirect('this'); //this is correct
        }
    }

    protected function createComponentGrid($name) {
        // So far, there's no use case that would list all persons.
        throw new NotImplementedException();
    }

    protected function createModel($id) {
        return $this->servicePerson->findByPrimary($id);
    }

    /*     * ******************************
     * Storing conflicts in session
     * ****************************** */

    private function setMergeConflicts($conflicts) {
        $section = $this->session->getSection('conflicts');
        if ($conflicts === null) {
            $section->remove();
        } else {
            $section->data = $conflicts;
        }
    }

    private function getMergeConflicts() {
        $section = $this->session->getSection('conflicts');
        if (isset($section->data)) {
            return $section->data;
        } else {
            return array();
        }
    }

}
