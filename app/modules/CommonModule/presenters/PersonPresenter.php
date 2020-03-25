<?php

namespace CommonModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Stalking\StalkingComponent\StalkingComponent;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServicePersonInfo;
use FormUtils;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Html;
use Persons\Deduplication\Merger;
use Persons\DenyResolver;
use Persons\ExtendedPersonHandler;
use ReflectionException;
use FKSDB\Components\Controls\Stalking;
use function str_replace;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @deprecated Do not use this presenter to create/modify persons.
 *             It's better to use ReferencedId and ReferencedContainer
 *             inside the particular form.
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonPresenter extends BasePresenter {
    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;

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

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

    /**
     * @var ModelPerson
     */
    private $person;
    /**
     * @var string
     */
    private $mode;

    /**
     * @param ServicePerson $servicePerson
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param ServicePersonInfo $servicePersonInfo
     */
    public function injectServicePersonInfo(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    /**
     * @param Merger $personMerger
     */
    public function injectPersonMerger(Merger $personMerger) {
        $this->personMerger = $personMerger;
    }

    /**
     * @param ReferencedPersonFactory $referencedPersonFactory
     */
    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    /* *********** TITLE ***************/
    public function titleSearch() {
        $this->setTitle(_('Search person'), 'fa fa-search');
    }

    /**
     * @throws BadRequestException
     */
    public function titleDetail() {
        $this->setTitle(sprintf(_('Detail of person %s'), $this->getPerson()->getFullName()), 'fa fa-eye');
    }

    public function titleMerge() {
        $this->setTitle(sprintf(_('Sloučení osob %s (%d) a %s (%d)'), $this->trunkPerson->getFullName(), $this->trunkPerson->person_id, $this->mergedPerson->getFullName(), $this->mergedPerson->person_id));
    }

    /* *********** AUTH ***************/
    public function authorizedSearch() {
        $this->setAuthorized($this->isAnyContestAuthorized('person', 'stalk.search'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedDetail() {
        $person = $this->getPerson();

        $full = $this->isAnyContestAuthorized($person, 'stalk.full');

        $restrict = $this->isAnyContestAuthorized($person, 'stalk.restrict');

        $basic = $this->isAnyContestAuthorized($person, 'stalk.basic');

        $this->setAuthorized($full || $restrict || $basic);
    }

    /**
     * @param $trunkId
     * @param $mergedId
     * @throws BadRequestException
     */
    public function authorizedMerge(int $trunkId, int $mergedId) {
        $this->trunkPerson = $this->servicePerson->findByPrimary($trunkId);
        $this->mergedPerson = $this->servicePerson->findByPrimary($mergedId);
        if (!$this->trunkPerson || !$this->mergedPerson) {
            throw new BadRequestException('Neexistující osoba.', 404);
        }
        $authorized = $this->isAnyContestAuthorized($this->trunkPerson, 'merge') &&
            $this->isAnyContestAuthorized($this->mergedPerson, 'merge');
        $this->setAuthorized($authorized);
    }

    /**
     * @param $trunkId
     * @param $mergedId
     * @throws BadRequestException
     */
    public function authorizedDontMerge(int $trunkId, int $mergedId) {
        $this->authorizedMerge($trunkId, $mergedId);
    }

    /* ********************* ACTIONS **************/
    /**
     * @param $trunkId
     * @param $mergedId
     */
    public function actionMerge(int $trunkId, int $mergedId) {
        $this->personMerger->setMergedPair($this->trunkPerson, $this->mergedPerson);
        $this->updateMergeForm($this->getComponent('mergeForm')->getForm());
    }

    /**
     * @param $trunkId
     * @param $mergedId
     * @throws AbortException
     * @throws ReflectionException
     * @throws \Exception
     */
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
        $this->backLinkRedirect(true);
    }
    /* ******************* COMPONENTS *******************/
    /**
     * @return StalkingComponent
     * @throws BadRequestException
     */
    public function createComponentStalkingComponent(): StalkingComponent {
        return new StalkingComponent($this->getContext(), $this->getPerson(), $this->getMode());
    }

    /**
     * @return Stalking\Address
     * @throws BadRequestException
     */
    public function createComponentAddress(): Stalking\Address {
        return new Stalking\Address($this->getContext(), $this->getPerson(), $this->getMode());
    }

    /**
     * @return Stalking\Role
     * @throws BadRequestException
     */
    public function createComponentRole(): Stalking\Role {
        return new Stalking\Role($this->getContext(), $this->getPerson(), $this->getMode());
    }

    /**
     * @return Stalking\Flag
     * @throws BadRequestException
     */
    public function createComponentFlag(): Stalking\Flag {
        return new Stalking\Flag($this->getContext(), $this->getPerson(), $this->getMode());
    }

    /**
     * @return Stalking\Schedule
     * @throws BadRequestException
     */
    public function createComponentSchedule(): Stalking\Schedule {
        return new Stalking\Schedule($this->getContext(), $this->getPerson(), $this->getMode());
    }

    /**
     * @return Stalking\Validation
     * @throws BadRequestException
     */
    public function createComponentValidation(): Stalking\Validation {
        return new Stalking\Validation($this->getContext(), $this->getPerson(), $this->getMode());
    }


    /**
     * @return FormControl
     * @throws BadRequestException
     */
    public function createComponentFormSearch(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $fieldsDefinition = [];
        //$acYear = $this->getSelectedAcademicYear();
        $searchType = ReferencedPersonFactory::SEARCH_ID;
        $allowClear = true;
        $modifiabilityResolver = $visibilityResolver = new DenyResolver();
        $components = $this->referencedPersonFactory->createReferencedPerson($fieldsDefinition, null, $searchType, $allowClear, $modifiabilityResolver, $visibilityResolver);
        $components[0]->addRule(Form::FILLED, _('Osobu je třeba zadat.'));
        $components[1]->setOption('label', _('Osoba'));

        $container->addComponent($components[0], ExtendedPersonHandler::EL_PERSON);
        $container->addComponent($components[1], ExtendedPersonHandler::CONT_PERSON);

        $submit = $form->addSubmit('send', _('Stalkovat'));
        $submit->onClick[] = function (SubmitButton $button) {
            $form = $button->getForm();
            $values = $form->getValues();
            $id = $values[ExtendedPersonHandler::CONT_AGGR][ExtendedPersonHandler::EL_PERSON];
            $this->redirect('detail', ['id' => $id]);
        };

        return $control;
    }

    /**
     * @return string
     * @throws BadRequestException
     */
    private function getMode() {
        if (!$this->mode) {
            if ($this->isAnyContestAuthorized($this->getPerson(), 'stalk.basic')) {
                $this->mode = Stalking\AbstractStalkingComponent::PERMISSION_BASIC;
            }
            if ($this->isAnyContestAuthorized($this->getPerson(), 'stalk.restrict')) {
                $this->mode = Stalking\AbstractStalkingComponent::PERMISSION_RESTRICT;
            }
            if ($this->isAnyContestAuthorized($this->getPerson(), 'stalk.full')) {
                $this->mode = Stalking\AbstractStalkingComponent::PERMISSION_FULL;
            }
        }
        return $this->mode;
    }

    /**
     * @return ModelPerson
     * @throws BadRequestException
     */
    private function getPerson(): ModelPerson {
        if (!$this->person) {
            $id = $this->getParameter('id');
            $row = $this->servicePerson->findByPrimary($id);
            if (!$row) {
                throw new BadRequestException(_('Osoba neexistuje'), 404);
            }
            $this->person = ModelPerson::createFromActiveRow($row);
        }

        return $this->person;
    }


    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentMergeForm() {
        $control = new FormControl();
        $form = $control->getForm();

        $form->addSubmit('send', _('Sloučit osoby'))->getControlPrototype()->addAttributes(['class' => 'btn-lg']);

        $form->addSubmit('cancel', _('Storno'))
            ->getControlPrototype()->addAttributes(['class' => 'btn-lg']);
        $form->onSuccess[] = function (Form $form) {
            $this->handleMergeFormSuccess($form);
        };
        return $control;
    }

    /**
     * @param Form $form
     */
    private function updateMergeForm(Form $form) {
        if (false && !$form->isSubmitted()) { // new form is without any conflict, we use it to clear the session
            $this->setMergeConflicts(null);
            return;
        }

        $conflicts = $this->getMergeConflicts();

        foreach ($conflicts as $table => $pairs) {
            $form->addGroup($table);
            $tableContainer = new ContainerWithOptions();

            $form->addComponent($tableContainer, $table);

            foreach ($pairs as $pairId => $data) {
                if (!isset($data[Merger::IDX_TRUNK])) {
                    continue;
                }
                $pairSuffix = '';
                if (count($pairs) > 1) {
                    $pairSuffix = " ($pairId)";
                }
                $pairContainer = new ContainerWithOptions();
                $tableContainer->addComponent($pairContainer, $pairId);
                $pairContainer->setOption('label', str_replace('_', ' ', $table));
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
                    $trunk->addAttributes(['class' => 'mergeSource']);
                    $trunk->data['field'] = $textElement->getHtmlId();
                    $elVal = Html::el('span');
                    $elVal->setText($value);
                    $trunk->addText(_('Trunk') . ': ');
                    $trunk->addText($elVal);
                    $elVal->addAttributes(['class' => 'value']);

                    $description->addHtml($trunk);

                    $merged = Html::el('div');
                    $merged->addAttributes(['class' => 'mergeSource']);
                    $merged->data['field'] = $textElement->getHtmlId();
                    $elVal = Html::el('span');
                    $elVal->setText($data[Merger::IDX_MERGED][$column]);
                    $elVal->addAttributes(['class' => 'value']);
                    $merged->addText(_('Merged') . ': ');
                    $merged->addText($elVal);
                    $description->addHtml($merged);

                    $textElement->setOption('description', $description);
                }
            }
        }
        $this->registerJSFile('js/mergeForm.js');
    }

    /**
     * @param Form $form
     * @throws AbortException
     * @throws ReflectionException
     */
    private function handleMergeFormSuccess(Form $form) {
        if ($form['cancel']->isSubmittedBy()) {
            $this->setMergeConflicts(null); // flush the session
            $this->backLinkRedirect(true);
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
            FlashMessageDump::dump($logger, $this);
            $this->backLinkRedirect(true);
        } else {
            $this->setMergeConflicts($merger->getConflicts());
            $this->flashMessage(_('Je třeba ručně vyřešit konflikty.'), self::FLASH_INFO);
            $this->redirect('this'); //this is correct
        }
    }

    /*     * ******************************
     * Storing conflicts in session
     * ****************************** */

    /**
     * @param $conflicts
     */
    private function setMergeConflicts($conflicts) {
        $section = $this->session->getSection('conflicts');
        if ($conflicts === null) {
            $section->remove();
        } else {
            $section->data = $conflicts;
        }
    }

    /**
     * @return array
     */
    private function getMergeConflicts() {
        $section = $this->session->getSection('conflicts');
        if (isset($section->data)) {
            return $section->data;
        } else {
            return [];
        }
    }
}
