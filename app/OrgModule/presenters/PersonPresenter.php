<?php

namespace OrgModule;

use AbstractModelSingle;
use Authentication\AccountManager;
use FKS\Logging\MemoryLogger;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use FormUtils;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Logging\FlashDumpFactory;
use Mail\MailTemplateFactory;
use Mail\SendFailedException;
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
use ServicePersonHistory;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonPresenter extends EntityPresenter {

    const CONT_PERSON = 'person';
    const CONT_ADDRESSES = 'addresses';
    const CONT_PERSON_INFO = 'personInfo';
    const CONT_PERSON_HISTORY = 'personHistory';

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
     * @var ServicePersonHistory
     */
    private $servicePersonHistory;

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
     * @var FlashDumpFactory
     */
    private $flashDumpFactory;

    /**
     * @var ModelPerson
     */
    private $trunkPerson;

    /**
     * @var ModelPerson
     */
    private $mergedPerson;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;

    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function injectServicePersonInfo(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function injectServicePersonHistory(ServicePersonHistory $servicePersonHistory) {
        $this->servicePersonHistory = $servicePersonHistory;
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

    public function injectFlashDumpFactory(FlashDumpFactory $flashDumpFactory) {
        $this->flashDumpFactory = $flashDumpFactory;
    }

    public function injectAccountManager(AccountManager $accountManager) {
        $this->accountManager = $accountManager;
    }

    public function injectMailTemplateFactory(MailTemplateFactory $mailTemplateFactory) {
        $this->mailTemplateFactory = $mailTemplateFactory;
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

    public function titleMerge() {
        $this->setTitle(sprintf(_('Sloučení osob %s (%d) a %s (%d)'), $this->trunkPerson->getFullname(), $this->trunkPerson->person_id, $this->mergedPerson->getFullname(), $this->mergedPerson->person_id));
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
                    $factory->buildAddress($replContainer, AddressFactory::SHOW_EXTENDED_ROWS, $group);
                    $replContainer->addComponent($factory->createTypeElement(), 'type');

                    $replContainer->addSubmit('remove', _('Odebrat adresu'))->addRemoveOnClick();
                }, $defaultAddresses, true);
        $replicator->containerClass = 'FKSDB\Components\Forms\Containers\AddressContainer';

        $form->addComponent($replicator, self::CONT_ADDRESSES);

        $replicator->addSubmit('add', _('Přidat adresu'))->addCreateOnClick();

        /**
         * Person history
         */
        $group = $form->addGroup(_('Proměnlivé informace'));

        $options = 0;
        $historyContainer = $this->personFactory->createPersonHistory($options, $group, $this->getSelectedAcademicYear());
        $form->addComponent($historyContainer, self::CONT_PERSON_HISTORY);

        /**
         * Personal information
         */
        $group = $form->addGroup(_('Osobní informace'));
        $login = $person->getLogin();
        $rule = $this->uniqueEmailFactory->create($person);

        $options = PersonFactory::SHOW_EMAIL | PersonFactory::SHOW_LOGIN_CREATION;
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
        $form->addSubmit('cancel', _('Storno'))
                ->getControlPrototype()->addClass('btn-default');
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

                $pairSuffix = '';
                if (count($pairs) > 1) {
                    $pairSuffix = " ($pairId)";
                }
                $pairContainer = $tableContainer->addContainer($pairId);
                foreach ($data[Merger::IDX_TRUNK] as $column => $value) {
                    if (isset($data[Merger::IDX_RESOLUTION]) && array_key_exists($column, $data[Merger::IDX_RESOLUTION])) {
                        $default = $data[Merger::IDX_RESOLUTION][$column];
                    } else {
                        $default = $value; // default is trunk
                    }

                    $textElement = $pairContainer->addText($column, $column . $pairSuffix)
                            ->setDefaultValue($default);

                    $description = Html::el('div');

                    $trunk = Html::el('div');
                    $trunk->class('mergeSource');
                    $trunk->data['field'] = $textElement->getHtmlId();
                    $elVal = Html::el('span');
                    $elVal->setText($value);
                    $elVal->class('value');
                    $trunk->add(_('Trunk') . ': ');
                    $trunk->add($elVal);
                    $description->add($trunk);

                    $merged = Html::el('div');
                    $merged->class('mergeSource');
                    $merged->data['field'] = $textElement->getHtmlId();
                    $elVal = Html::el('span');
                    $elVal->setText($data[Merger::IDX_MERGED][$column]);
                    $elVal->class('value');
                    $merged->add(_('Merged') . ': ');
                    $merged->add($elVal);
                    $description->add($merged);

                    $textElement->setOption('description', $description);
                }
            }
        }
        $this->registerJSFile('js/mergeForm.js');
    }

    protected function setDefaults(AbstractModelSingle $person, Form $form) {
        $defaults = array();

        $defaults[self::CONT_PERSON] = $person;

        $addresses = array();
        foreach ($person->getMPostContacts() as $mPostContact) {
            $addresses[] = $mPostContact;
        }
        $defaults[self::CONT_ADDRESSES] = $addresses;

        $history = $person->getHistory($this->getSelectedAcademicYear());
        if ($history) {
            $defaults[self::CONT_PERSON_HISTORY] = $history;
        }

        $info = $person->getInfo();
        if ($info) {
            $defaults[self::CONT_PERSON_INFO] = $info;
            $this->personFactory->modifyLoginContainer($form[self::CONT_PERSON_INFO], $person);
        }

        $form->setDefaults($defaults);
    }

    /**
     * @internal
     * @param SubmitButton $button
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

            // load data common to login & person_info
            $personInfoData = $values[self::CONT_PERSON_INFO];
            $personInfoData = FormUtils::emptyStrToNull($personInfoData);

            /*
             * Login
             */
            $email = $personInfoData['email'];
            $createLogin = $personInfoData[PersonFactory::CONT_LOGIN][PersonFactory::EL_CREATE_LOGIN];

            if ($email && !$person->getLogin() && $createLogin) {
                $lang = $personInfoData[PersonFactory::CONT_LOGIN][PersonFactory::EL_CREATE_LOGIN_LANG];
                $template = $this->mailTemplateFactory->createLoginInvitation($this, $lang);
                try {
                    $this->accountManager->createLoginWithInvitation($template, $person, $email);
                    $this->flashMessage(_('Zvací e-mail odeslán.'), self::FLASH_INFO);
                } catch (SendFailedException $e) {
                    $this->flashMessage(_('Zvací e-mail se nepodařilo odeslat.'), self::FLASH_ERROR);
                }
            }

            /*
             * Person history
             */
            $personHistoryData = $values[self::CONT_PERSON_HISTORY];
            $personHistoryData = FormUtils::emptyStrToNull($personHistoryData);

            $personHistory = $person->getHistory($this->getSelectedAcademicYear());
            if (!$personHistory) {
                $personHistory = $this->servicePersonHistory->createNew($personHistoryData);
                $personHistory->person_id = $person->person_id;
                $personHistory->ac_year = $this->getSelectedAcademicYear();
            } else {
                $this->servicePersonHistory->updateModel($personHistory, $personHistoryData);
            }

            $this->servicePersonHistory->save($personHistory);

            /*
             * Personal info
             */
            $personInfo = $person->getInfo();
            if (!$personInfo) {
                $personInfo = $this->servicePersonInfo->createNew($personInfoData);
                $personInfo->person_id = $person->person_id;
            } else {
                unset($personInfoData['agreed']); // not to overwrite existing confirmation
                $this->servicePersonInfo->updateModel($personInfo, $personInfoData);
            }

            $this->servicePersonInfo->save($personInfo);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage(sprintf('Údaje osoby %s upraveny.', $person->getFullname()), self::FLASH_SUCCESS);

            $this->backlinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage(_('Chyba při úpravě osoby.'), self::FLASH_ERROR);
        }
    }

    public function handleMergeFormSuccess(Form $form) {
        if ($form['cancel']->isSubmittedBy()) {
            $this->setMergeConflicts(null); // flush the session
            $this->backlinkRedirect(true);
        }

        $values = $form->getValues();
        $values = FormUtils::emptyStrToNull($values);

        $merger = $this->personMerger;
        $merger->setConflictResolution($values);
        $logger = new MemoryLogger();
        $merger->setLogger($logger);
        if ($merger->merge()) {
            $this->setMergeConflicts(null); // flush the session
            $this->flashMessage(_('Osoby úspešně sloučeny.'), self::FLASH_SUCCESS);
            $flashDump = $this->flashDumpFactory->createPersonMerge();
            $flashDump->dump($logger, $this);
            $this->backlinkRedirect(true);
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
