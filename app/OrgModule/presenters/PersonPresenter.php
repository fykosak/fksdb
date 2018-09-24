<?php

namespace OrgModule;

use Authentication\AccountManager;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use FKSDB\Logging\FlashDumpFactory;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\ModelPerson;
use FormUtils;
use Mail\MailTemplateFactory;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
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
 * @deprecated Do not use this presenter to create/modify persons.
 *             It's better to use ReferencedId and ReferencedContainer
 *             inside the particular form.
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

    public function authorizedDontMerge($trunkId, $mergedId) {
        $this->authorizedMerge($trunkId, $mergedId);
    }

    public function actionMerge($trunkId, $mergedId) {
        $this->personMerger->setMergedPair($this->trunkPerson, $this->mergedPerson);
        $this->updateMergeForm($this['mergeForm']->getForm());
    }

    public function actionDontMerge($trunkId, $mergedId) {
        $mergedPI = $this->servicePersonInfo->findByPrimary($mergedId);
        $mergedData = ['duplicates' => trim($mergedPI->duplicates . ",not-same($trunkId)", ',')];
        $this->servicePersonInfo->updateModel($mergedPI, $mergedData);
        $this->servicePersonInfo->save($mergedPI);

        $trunkPI = $this->servicePersonInfo->findByPrimary($trunkId);
        $trunkData = ['duplicates' => trim($trunkPI->duplicates . ",not-same($mergedId)", ',')];
        $this->servicePersonInfo->updateModel($trunkPI, $trunkData);
        $this->servicePersonInfo->save($trunkPI);

        $this->flashMessage(_('Osoby úspešně nesloučeny.'), self::FLASH_SUCCESS);
        $this->backlinkRedirect(true);
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

    protected function createComponentCreateComponent($name) {
        // So far, there's no use case that creates bare person.
        throw new NotImplementedException();
    }

    protected function createComponentEditComponent($name) {
        // Persons are edited via referenced person containers.
        throw new NotImplementedException();
    }

    protected function createComponentMergeForm($name) {
        $control = new FormControl();
        $form = $control->getForm();

        $form->addSubmit('send', _('Sloučit osoby'))->getControlPrototype()->addAttributes(['class' => 'btn-lg']);

        $form->addSubmit('cancel', _('Storno'))
            ->getControlPrototype()->addAttributes(['class' => 'btn-lg']);
        $form->onSuccess[] = array($this, 'handleMergeFormSuccess');
        return $control;
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

    protected function loadModel($id) {
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
