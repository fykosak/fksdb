<?php

namespace OrgModule;

use AbstractModelSingle;
use FKS\Components\Controls\FormControl;
use FKS\Components\Forms\Containers\ContainerWithOptions;
use FKS\Components\Forms\Controls\ModelDataConflictException;
use FKS\Config\GlobalParameters;
use FKSDB\Components\Forms\Factories\ContestantFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\ReferencedPersonFactory;
use FKSDB\Components\Grids\ContestantsGrid;
use ModelException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SubmitButton;
use Nette\NotImplementedException;
use OrgModule\EntityPresenter;
use Persons\AclResolver;
use ServiceContestant;
use ServicePerson;

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
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var ContestantFactory
     */
    private $contestantFactory;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

    /**
     * @var GlobalParameters
     */
    private $globalParameters;

    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
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
        $connection = $this->servicePerson->getConnection();
        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }
            $data = array(
                'contest_id' => $this->getSelectedContest()->contest_id,
                'year' => $this->getSelectedYear(),
            );
            $contestant = $this->serviceContestant->createNew($data);
            $values = $form->getValues();

            $contestant->person_id = $values[self::CONT_MAIN][self::EL_PERSON];

            $this->serviceContestant->save($contestant);

            //TODO login

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

