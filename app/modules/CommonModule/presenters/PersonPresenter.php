<?php

namespace CommonModule;

use FKSDB\Components\Controls\Entity\Person\PersonForm;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Stalking\StalkingComponent\StalkingComponent;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
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
use Nette\Security\IResource;
use Nette\Utils\Html;
use Persons\Deduplication\Merger;
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
 * @method ModelPerson getEntity()
 */
class PersonPresenter extends BasePresenter {
    use EntityTrait;

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
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var ModelPerson
     */
    private $person;
    /**
     * @var int
     */
    private $mode;

    /**
     * @param ServicePerson $servicePerson
     * @return void
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param ServicePersonInfo $servicePersonInfo
     * @return void
     */
    public function injectServicePersonInfo(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    /**
     * @param Merger $personMerger
     * @return void
     */
    public function injectPersonMerger(Merger $personMerger) {
        $this->personMerger = $personMerger;
    }

    /**
     * @param PersonFactory $personFactory
     * @return void
     */
    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    /* *********** TITLE ***************/
    public function titleSearch() {
        $this->setTitle(_('Find person'), 'fa fa-search');
    }

    /**
     * @return void
     */
    public function titleDetail() {
        $this->setTitle(sprintf(_('Detail of person %s'), $this->getEntity()->getFullName()), 'fa fa-eye');
    }

    /**
     * @return void
     */
    public function titleEdit() {
        $this->setTitle(sprintf(_('Edit person "%s"'), $this->getEntity()->getFullName()), 'fa fa-eye');
    }

    public function titleMerge() {
        $this->setTitle(sprintf(_('Sloučení osob %s (%d) a %s (%d)'), $this->trunkPerson->getFullName(), $this->trunkPerson->person_id, $this->mergedPerson->getFullName(), $this->mergedPerson->person_id));
    }

    /* *********** AUTH ***************/
    public function authorizedSearch() {
        $this->setAuthorized($this->isAnyContestAuthorized('person', 'stalk.search'));
    }

    public function authorizedEdit() {
        $this->setAuthorized($this->isAnyContestAuthorized('person', 'edit'));
    }

    /**
     * @return void
     */
    public function authorizedDetail() {
        $person = $this->getEntity();
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
    public function authorizedMerge($trunkId, $mergedId) {
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
    public function authorizedDontMerge($trunkId, $mergedId) {
        $this->authorizedMerge($trunkId, $mergedId);
    }

    /* ********************* ACTIONS **************/
    /**
     * @param $trunkId
     * @param $mergedId
     * @return void
     */
    public function actionMerge($trunkId, $mergedId) {
        $this->personMerger->setMergedPair($this->trunkPerson, $this->mergedPerson);
        $this->updateMergeForm($this->getComponent('mergeForm')->getForm());
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function actionEdit() {
        $this->traitActionEdit();
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
        $this->servicePersonInfo->updateModel2($mergedPI, $mergedData);

        $trunkPI = $this->servicePersonInfo->findByPrimary($trunkId);
        $trunkData = ['duplicates' => trim($trunkPI->duplicates . ",not-same($mergedId)", ',')];
        $this->servicePersonInfo->updateModel2($trunkPI, $trunkData);

        $this->flashMessage(_('Osoby úspešně nesloučeny.'), ILogger::SUCCESS);
        $this->backLinkRedirect(true);
    }

    /**
     * @return void
     */
    public function renderDetail() {
        $person = $this->getEntity();
        $this->template->userPermissions = $this->getUserPermissions();
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
        return new StalkingComponent($this->getContext(), $this->getEntity(), $this->getUserPermissions());
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
        $control = new FormControl();
        $form = $control->getForm();
        $form->addComponent($this->personFactory->createPersonSelect(true, _('Person'), new PersonProvider($this->servicePerson)), 'person_id');

        $stalkSubmit = $form->addSubmit('stalk', _('Stalk'));
        $editSubmit = $form->addSubmit('edit', _('Edit'));

        $stalkSubmit->onClick[] = function (SubmitButton $button) {
            $values = $button->getForm()->getValues();
            $this->redirect('detail', ['id' => $values['person_id']]);
        };
        $editSubmit->onClick[] = function (SubmitButton $button) {
            $values = $button->getForm()->getValues();
            $this->redirect('edit', ['id' => $values['person_id']]);
        };

        return $control;
    }

    private function getUserPermissions(): int {
        if (!$this->mode) {
            if ($this->isAnyContestAuthorized($this->getEntity(), 'stalk.basic')) {
                $this->mode = Stalking\AbstractStalkingComponent::PERMISSION_BASIC;
            }
            if ($this->isAnyContestAuthorized($this->getEntity(), 'stalk.restrict')) {
                $this->mode = Stalking\AbstractStalkingComponent::PERMISSION_RESTRICT;
            }
            if ($this->isAnyContestAuthorized($this->getEntity(), 'stalk.full')) {
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
     * @return void
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
    public function handleMergeFormSuccess(Form $form) {
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
     * @return void
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

    public function createComponentCreateForm(): Control {
        return new PersonForm($this->getContext(), true);

    }

    public function createComponentEditForm(): Control {
        return new PersonForm($this->getContext(), false, new FieldLevelPermission($this->getUserPermissions(), $this->getUserPermissions()));
    }

    /**
     * @return BaseGrid
     * @throws NotImplementedException
     */
    protected function createComponentGrid(): BaseGrid {
        throw new NotImplementedException();
    }

    protected function getORMService(): ServicePerson {
        return $this->servicePerson;
    }

    /**
     * @param IResource|string $resource
     * @param string|null $privilege
     * all auth method is overwritten
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isAnyContestAuthorized($resource, $privilege);;
    }
}
