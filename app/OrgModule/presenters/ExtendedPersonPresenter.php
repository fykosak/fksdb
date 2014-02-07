<?php

namespace OrgModule;

use AbstractModelSingle;
use Authentication\AccountManager;
use FKS\Components\Controls\FormControl;
use FKS\Components\Forms\Containers\ContainerWithOptions;
use FKS\Components\Forms\Controls\ModelDataConflictException;
use FKS\Config\GlobalParameters;
use FKSDB\Components\Forms\Factories\ContestantFactory;
use FKSDB\Components\Forms\Factories\ReferencedPersonFactory;
use FKSDB\Components\Grids\ContestantsGrid;
use Mail\MailTemplateFactory;
use Mail\SendFailedException;
use ModelException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SubmitButton;
use Nette\NotImplementedException;
use OrgModule\EntityPresenter;
use Persons\AclResolver;
use ServiceContestant;

class ContestantPresenter extends EntityPresenter {

    const EL_PERSON = 'person_id';
    const CONT_PERSON = 'person';
    const CONT_MAIN = 'main';

    protected $modelResourceId = 'contestant';

    /**
     * @var ServiceContestant
     */
    private $serviceContestant;

    /**
     * @var ContestantFactory
     */
    private $contestantFactory;

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

    /**
     * @var GlobalParameters
     */
    private $globalParameters;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;

    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    public function injectContestantFactory(ContestantFactory $contestantFactory) {
        $this->contestantFactory = $contestantFactory;
    }

    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    public function injectGlobalParameters(GlobalParameters $globalParameters) {
        $this->globalParameters = $globalParameters;
    }

    public function injectAccountManager(AccountManager $accountManager) {
        $this->accountManager = $accountManager;
    }

    public function injectMailTemplateFactory(MailTemplateFactory $mailTemplateFactory) {
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    public function titleEdit($id) {
        $this->setTitle(sprintf(_('Úprava řešitele %s'), $this->getModel()->getPerson()->getFullname()));
    }

    public function titleCreate() {
        $this->setTitle(_('Založit řešitele'));
    }

    public function titleList() {
        $this->setTitle(_('Řešitelé'));
    }

    protected function setDefaults(AbstractModelSingle $model, Form $form) {
        $form[self::EL_PERSON]->setDefaultValue($this->getModel()->getPerson()->getPrimary(false));
    }

    private function getFieldsDefinition() {
        $contestId = $this->getSelectedContest()->contest_id;
        $contestName = $this->globalParameters['contestMapping'][$contestId];
        return $this->globalParameters[$contestName]['contestantCreation'];
    }

    protected function createComponentCreateComponent($name) {
        $control = new FormControl();
        $form = $control->getForm();
        $control->setGroupMode(FormControl::GROUP_CONTAINER);

        $container = new ContainerWithOptions();
        $form->addComponent($container, self::CONT_MAIN);

        $fieldsDefinition = $this->getFieldsDefinition();
        $acYear = $this->getSelectedAcademicYear();
        $searchType = ReferencedPersonFactory::SEARCH_ID;
        $allowClear = true;
        $modifiabilityResolver = $visibilityResolver = new AclResolver($this->contestAuthorizator, $this->getSelectedContest(), $this->getModel() ? : 'contestant');
        $components = $this->referencedPersonFactory->createReferencedPerson($fieldsDefinition, $acYear, $searchType, $allowClear, $modifiabilityResolver, $visibilityResolver);
        $components[1]->setOption('label', _('Osoba'));

        $container->addComponent($components[0], self::EL_PERSON);
        $container->addComponent($components[1], self::CONT_PERSON);

        $submit = $form->addSubmit('send', _('Založit'));
        $that = $this;
        $submit->onClick[] = function(SubmitButton $button) use($that) {
                    $form = $button->getForm();
                    $that->handleFormSuccess($form);
                };

        return $control;
    }

    protected function createComponentGrid($name) {
        $grid = new ContestantsGrid($this->serviceContestant);

        return $grid;
    }

    protected function createComponentEditComponent($name) {
        throw new NotImplementedException();
    }

    private function handleFormSuccess(Form $form) {
        $connection = $this->serviceContestant->getConnection();
        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            // initialize model
            $data = array(
                'contest_id' => $this->getSelectedContest()->contest_id,
                'year' => $this->getSelectedYear(),
            );
            $contestant = $this->serviceContestant->createNew($data);

            // store values
            $values = $form->getValues();

            $contestant->person_id = $values[self::CONT_MAIN][self::EL_PERSON];

            // store model
            $this->serviceContestant->save($contestant);

            // create login
            $person = $contestant->getPerson();
            $email = $person->getInfo() ? $person->getInfo()->email : null;
            $login = $contestant->getPerson()->getLogin();
            if ($email && !$login) {
                $template = $this->mailTemplateFactory->createLoginInvitation($this, $this->globalParameters['invitation']['defaultLang']);
                try {
                    $this->accountManager->createLoginWithInvitation($template, $person, $email);
                    $this->flashMessage(_('Zvací e-mail odeslán.'), self::FLASH_INFO);
                } catch (SendFailedException $e) {
                    $this->flashMessage(_('Zvací e-mail se nepodařilo odeslat.'), self::FLASH_ERROR);
                }
            }

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage(sprintf('Řešitel %s založen.', $contestant->getPerson()->getFullname()), self::FLASH_SUCCESS);

            $this->backlinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage(_('Chyba při zakládání řešitele.'), self::FLASH_ERROR);
        } catch (ModelDataConflictException $e) {
            $form->addError(_('Zadaná data se neshodují s již uloženými.'));
            $e->getReferencedId()->getReferencedContainer()->setConflicts($e->getConflicts());
            $e->getReferencedId()->rollback();
            $connection->rollBack();
        }
    }

    protected function createModel($id) {
        return $this->serviceContestant->findByPrimary($id);
    }

}

