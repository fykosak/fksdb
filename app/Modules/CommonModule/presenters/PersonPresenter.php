<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Components\Controls\Entity\PersonFormComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Person\PizzaControl;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\ILogger;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServicePersonInfo;
use FKSDB\UI\PageTitle;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\InvalidStateException;
use Nette\Security\IResource;
use Nette\Utils\Html;
use FKSDB\Persons\Deduplication\Merger;
use ReflectionException;
use FKSDB\Components\Controls\Stalking\StalkingContainer;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * Do not use this presenter to create/modify persons.
 *             It's better to use ReferencedId and ReferencedContainer
 *             inside the particular form.
 * TODO fix referenced person
 * @author Michal Koutný <michal@fykos.cz>
 * @method ModelPerson getEntity()
 */
class PersonPresenter extends BasePresenter {
    use EntityPresenterTrait;

    private ServicePerson $servicePerson;

    private ServicePersonInfo $servicePersonInfo;

    private Merger $personMerger;

    /** @var ModelPerson */
    private $trunkPerson;

    /** @var ModelPerson */
    private $mergedPerson;

    private PersonFactory $personFactory;

    private int $userPermissions;

    public function injectServicePerson(ServicePerson $servicePerson): void {
        $this->servicePerson = $servicePerson;
    }

    public function injectServicePersonInfo(ServicePersonInfo $servicePersonInfo): void {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function injectPersonMerger(Merger $personMerger): void {
        $this->personMerger = $personMerger;
    }

    public function injectPersonFactory(PersonFactory $personFactory): void {
        $this->personFactory = $personFactory;
    }

    /* *********** TITLE ***************/
    /**
     * @return void
     *
     * @throws ForbiddenRequestException
     */
    public function titleSearch(): void {
        $this->setPageTitle(new PageTitle(_('Find person'), 'fa fa-search'));
    }

    /**
     * @return void
     *
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Detail of person %s'), $this->getEntity()->getFullName()), 'fa fa-eye'));
    }

    /**
     * @return void
     *
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Edit person "%s"'), $this->getEntity()->getFullName()), 'fa fa-user'));
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(_('Create person'), 'fa fa-user-plus');
    }

    /**
     * @return void
     *
     * @throws ForbiddenRequestException
     */
    public function titleMerge(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Sloučení osob %s (%d) a %s (%d)'), $this->trunkPerson->getFullName(), $this->trunkPerson->person_id, $this->mergedPerson->getFullName(), $this->mergedPerson->person_id)));
    }

    /**
     * @return void
     *
     * @throws ForbiddenRequestException
     */
    public function titlePizza(): void {
        $this->setPageTitle(new PageTitle(_('Pizza'), 'fa fa-cutlery'));
    }

    /* *********** AUTH ***************/
    public function authorizedSearch(): void {
        $this->setAuthorized($this->isAnyContestAuthorized('person', 'stalk.search'));
    }

    public function authorizedEdit(): void {
        $this->setAuthorized($this->isAnyContestAuthorized($this->getEntity(), 'edit'));
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     */
    public function authorizedDetail(): void {
        $full = $this->isAnyContestAuthorized($this->getEntity(), 'stalk.full');
        $restrict = $this->isAnyContestAuthorized($this->getEntity(), 'stalk.restrict');
        $basic = $this->isAnyContestAuthorized($this->getEntity(), 'stalk.basic');

        $this->setAuthorized($full || $restrict || $basic);
    }

    /**
     * @param int $trunkId
     * @param int $mergedId
     * @throws NotFoundException
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
     * @param int $trunkId
     * @param int $mergedId
     * @throws NotFoundException
     */
    public function authorizedDontMerge($trunkId, $mergedId): void {
        $this->authorizedMerge($trunkId, $mergedId);
    }

    /* ********************* ACTIONS **************/
    /**
     * @param int $trunkId
     * @param int $mergedId
     * @return void
     */
    public function actionMerge($trunkId, $mergedId): void {
        $this->personMerger->setMergedPair($this->trunkPerson, $this->mergedPerson);
        $this->updateMergeForm($this->getComponent('mergeForm')->getForm());
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws ModelNotFoundException
     */
    public function actionEdit(): void {
        $this->traitActionEdit();
    }

    /**
     * @param int $trunkId
     * @param int $mergedId
     * @return void
     * @throws AbortException
     * @throws BadTypeException
     * @throws ReflectionException
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
     * @return void
     * @throws ModelNotFoundException
     */
    public function renderDetail(): void {
        $person = $this->getEntity();
        $this->template->isSelf = $this->getUser()->getIdentity()->getPerson()->person_id === $person->person_id;
        /** @var ModelPerson $userPerson */
        $userPerson = $this->getUser()->getIdentity()->getPerson();
        Debugger::log(sprintf('%s (%d) stalk %s (%d)',
            $userPerson->getFullName(), $userPerson->person_id,
            $person->getFullName(), $person->person_id), 'stalking-log');
    }

    /* ******************* COMPONENTS *******************/
    /**
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentFormSearch(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addComponent($this->personFactory->createPersonSelect(true, _('Person'), new PersonProvider($this->servicePerson)), 'person_id');

        $form->addSubmit('stalk', _('Stalk'))
            ->onClick[] = function (SubmitButton $button) {
            $values = $button->getForm()->getValues();
            $this->redirect('detail', ['id' => $values['person_id']]);
        };
        $form->addSubmit('edit', _('Edit'))
            ->onClick[] = function (SubmitButton $button) {
            $values = $button->getForm()->getValues();
            $this->redirect('edit', ['id' => $values['person_id']]);
        };

        return $control;
    }

    /**
     * @return FormControl
     * @throws BadTypeException
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
     * @return Control
     * @throws ModelNotFoundException
     */
    protected function createComponentCreateForm(): Control {
        return new PersonFormComponent($this->getContext(), true, $this->getUserPermissions());
    }

    /**
     * @return Control
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): Control {
        return new PersonFormComponent($this->getContext(), false, $this->getUserPermissions());
    }

    protected function createComponentPizzaSelect(): PizzaControl {
        return new PizzaControl($this->getContext());
    }

    /**
     * @return BaseGrid
     * @throws NotImplementedException
     */
    protected function createComponentGrid(): BaseGrid {
        throw new NotImplementedException();
    }

    /**
     * @return StalkingContainer
     * @throws ModelNotFoundException
     */
    public function createComponentStalkingContainer(): StalkingContainer {
        return new StalkingContainer($this->getContext(), $this->getEntity(), $this->getUserPermissions());
    }

    /**
     * @return int
     * @throws ModelNotFoundException
     */
    private function getUserPermissions(): int {
        if (!isset($this->userPermissions)) {
            $this->userPermissions = FieldLevelPermission::ALLOW_ANYBODY;
            try {
                $person = $this->getEntity();
                if ($this->isAnyContestAuthorized($person, 'stalk.basic')) {
                    $this->userPermissions = FieldLevelPermission::ALLOW_BASIC;
                }
                if ($this->isAnyContestAuthorized($person, 'stalk.restrict')) {
                    $this->userPermissions = FieldLevelPermission::ALLOW_RESTRICT;
                }
                if ($this->isAnyContestAuthorized($person, 'stalk.full')) {
                    $this->userPermissions = FieldLevelPermission::ALLOW_FULL;
                }

            } catch (InvalidStateException$exception) {
                $this->userPermissions = FieldLevelPermission::ALLOW_FULL;
            }
        }
        return $this->userPermissions;
    }

    private function updateMergeForm(Form $form): void {
        if (!$form->isSubmitted()) { // new form is without any conflict, we use it to clear the session
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
    private function handleMergeFormSuccess(Form $form): void {
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

    private function setMergeConflicts(array $conflicts): void {
        $section = $this->session->getSection('conflicts');
        if ($conflicts === null) {
            $section->remove();
        } else {
            $section->data = $conflicts;
        }
    }

    private function getMergeConflicts(): array {
        $section = $this->session->getSection('conflicts');
        if (isset($section->data)) {
            return $section->data;
        } else {
            return [];
        }
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
        return $this->isAnyContestAuthorized($resource, $privilege);
    }
}
