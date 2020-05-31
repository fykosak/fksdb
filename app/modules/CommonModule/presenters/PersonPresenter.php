<?php

namespace CommonModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Stalking\StalkingComponent\StalkingComponent;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\EntityTrait;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\ILogger;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServicePersonInfo;
use FormUtils;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Html;
use Persons\Deduplication\Merger;
use Persons\DenyResolver;
use Persons\ExtendedPersonHandler;
use ReflectionException;
use FKSDB\Components\Controls\Stalking;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * Do not use this presenter to create/modify persons.
 *             It's better to use ReferencedId and ReferencedContainer
 *             inside the particular form.
 * @author Michal Koutný <michal@fykos.cz>
 * @method ModelPerson loadEntity(int $id)
 * @method ModelPerson getEntity()
 */
class PersonPresenter extends BasePresenter {
    use EntityTrait;

    private ServicePerson $servicePerson;

    private ServicePersonInfo $servicePersonInfo;

    private Merger $personMerger;

    private ModelPerson $trunkPerson;

    private ModelPerson $mergedPerson;

    private ReferencedPersonFactory $referencedPersonFactory;

    private ModelPerson $person;
    /**
     * @var int
     */
    private $mode;

    public function injectServicePerson(ServicePerson $servicePerson): void {
        $this->servicePerson = $servicePerson;
    }

    public function injectServicePersonInfo(ServicePersonInfo $servicePersonInfo): void {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function injectPersonMerger(Merger $personMerger): void {
        $this->personMerger = $personMerger;
    }

    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory): void {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    /* *********** TITLE ***************/
    public function titleSearch(): void {
        $this->setTitle(_('Find person'), 'fa fa-search');
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function titleDetail(int $id): void {
        $this->setTitle(sprintf(_('Detail of person %s'), $this->loadEntity($id)->getFullName()), 'fa fa-eye');
    }

    public function titleMerge(): void {
        $this->setTitle(sprintf(_('Sloučení osob %s (%d) a %s (%d)'), $this->trunkPerson->getFullName(), $this->trunkPerson->person_id, $this->mergedPerson->getFullName(), $this->mergedPerson->person_id));
    }

    /* *********** AUTH ***************/
    public function authorizedSearch(): void {
        $this->setAuthorized($this->isAnyContestAuthorized('person', 'stalk.search'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedDetail(int $id): void {
        $person = $this->loadEntity($id);

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
    public function authorizedMerge($trunkId, $mergedId): void {
        $this->trunkPerson = $this->servicePerson->findByPrimary($trunkId);
        $this->mergedPerson = $this->servicePerson->findByPrimary($mergedId);
        if (!$this->trunkPerson || !$this->mergedPerson) {
            throw new NotFoundException('Neexistující osoba.');
        }
        $authorized = $this->getContestAuthorizator()->isAllowedForAnyContest($this->trunkPerson, 'merge') &&
            $this->getContestAuthorizator()->isAllowedForAnyContest($this->mergedPerson, 'merge');
        $this->setAuthorized($authorized);
    }

    /**
     * @param $trunkId
     * @param $mergedId
     * @throws BadRequestException
     */
    public function authorizedDontMerge($trunkId, $mergedId): void {
        $this->authorizedMerge($trunkId, $mergedId);
    }

    /* ********************* ACTIONS **************/
    /**
     * @param int $trunkId
     * @param int $mergedId
     */
    public function actionMerge($trunkId, $mergedId): void {
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
    public function actionDontMerge($trunkId, $mergedId): void {
        $mergedPI = $this->servicePersonInfo->findByPrimary($mergedId);
        $mergedData = ['duplicates' => trim($mergedPI->duplicates . ",not-same($trunkId)", ',')];
        $this->servicePersonInfo->updateModel2($mergedPI, $mergedData);

        $trunkPI = $this->servicePersonInfo->findByPrimary($trunkId);
        $trunkData = ['duplicates' => trim($trunkPI->duplicates . ",not-same($mergedId)", ',')];
        $this->servicePersonInfo->updateModel2($trunkPI, $trunkData);

        $this->flashMessage(_('Osoby úspešně nesloučeny.'), ILogger::SUCCESS);
        $this->backLinkRedirect(true);
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function renderDetail(int $id): void {
        $person = $this->loadEntity($id);
        $this->template->userPermissions = $this->getUserPermissions($person);
        $this->template->person = $person;
        $this->template->isSelf = $this->getUser()->getIdentity()->getPerson()->person_id === $person->person_id;
        /** @var ModelPerson $userPerson */
        $userPerson = $this->getUser()->getIdentity()->getPerson();
        Debugger::log(sprintf('%s (%d) stalk %s (%d)',
            $userPerson->getFullName(), $userPerson->person_id,
            $person->getFullName(), $person->person_id), 'stalking-log');
    }

    /* ******************* COMPONENTS *******************/

    public function createComponentStalkingComponent(): StalkingComponent {
        return new StalkingComponent($this->getContext());
    }

    public function createComponentAddress(): Stalking\Address {
        return new Stalking\Address($this->getContext());
    }

    public function createComponentRole(): Stalking\Role {
        return new Stalking\Role($this->getContext());
    }

    public function createComponentFlag(): Stalking\Flag {
        return new Stalking\Flag($this->getContext());
    }

    public function createComponentSchedule(): Stalking\Schedule {
        return new Stalking\Schedule($this->getContext());
    }

    public function createComponentValidation(): Stalking\Validation {
        return new Stalking\Validation($this->getContext());
    }

    public function createComponentTimeline(): Stalking\Timeline\TimelineControl {
        return new Stalking\Timeline\TimelineControl($this->getContext(), $this->getEntity());
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws \Exception
     */
    public function createComponentFormSearch(): FormControl {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);
        $modifiabilityResolver = $visibilityResolver = new DenyResolver();
        $acYear = $this->getYearCalculator()->getCurrentAcademicYear();
        $components = $this->referencedPersonFactory->createReferencedPerson([], $acYear, ReferencedPersonFactory::SEARCH_ID, true, $modifiabilityResolver, $visibilityResolver);
        $components[0]->addRule(Form::FILLED, _('Osobu je třeba zadat.'));
        $components[1]->setOption('label', _('Osoba'));

        $container->addComponent($components[0], ExtendedPersonHandler::EL_PERSON);
        $container->addComponent($components[1], ExtendedPersonHandler::CONT_PERSON);

        $submit = $form->addSubmit('send', _('Stalkovat'));
        $submit->onClick[] = function (SubmitButton $button) {
            $values = $button->getForm()->getValues();
            $id = $values[ExtendedPersonHandler::CONT_AGGR][ExtendedPersonHandler::EL_PERSON];
            $this->redirect('detail', ['id' => $id]);
        };

        return $control;
    }

    private function getUserPermissions(ModelPerson $person): int {
        if (!$this->mode) {
            if ($this->isAnyContestAuthorized($person, 'stalk.basic')) {
                $this->mode = Stalking\AbstractStalkingComponent::PERMISSION_BASIC;
            }
            if ($this->isAnyContestAuthorized($person, 'stalk.restrict')) {
                $this->mode = Stalking\AbstractStalkingComponent::PERMISSION_RESTRICT;
            }
            if ($this->isAnyContestAuthorized($person, 'stalk.full')) {
                $this->mode = Stalking\AbstractStalkingComponent::PERMISSION_FULL;
            }
        }
        return $this->mode;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentMergeForm(): FormControl {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $form->addSubmit('send', _('Sloučit osoby'))->getControlPrototype()->addAttributes(['class' => 'btn-lg']);

        $form->addSubmit('cancel', _('Storno'))
            ->getControlPrototype()->addAttributes(['class' => 'btn-lg']);
        $form->onSuccess[] = function (Form $form) {
            $this->handleMergeFormSuccess($form);
        };
        return $control;
    }

    private function updateMergeForm(Form $form): void {
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
                $pairContainer->setOption('label', \str_replace('_', ' ', $table));
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
     * @throws BadTypeException
     */
    public function handleMergeFormSuccess(Form $form): void {
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
     * @param array $conflicts
     * @return void
     */
    private function setMergeConflicts($conflicts): void {
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

    /**
     * @inheritDoc
     */
    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function createComponentGrid(): BaseGrid {
        throw new NotImplementedException();
    }

    protected function getORMService(): ServicePerson {
        return $this->servicePerson;
    }

    /**
     * @inheritDoc
     * all auth method is overwritten
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return false;
    }
}
