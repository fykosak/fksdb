<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Components\Controls\Entity\PersonFormComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Person\PizzaControl;
use FKSDB\Components\Controls\Stalking\StalkingContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\IResource;
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
    private PersonFactory $personFactory;
    private int $userPermissions;

    final public function injectQuarterly(
        ServicePerson $servicePerson,
        PersonFactory $personFactory
    ): void {
        $this->servicePerson = $servicePerson;
        $this->personFactory = $personFactory;
    }

    /* *********** TITLE ***************/
    /**
     * @return void
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

    /* ********************* ACTIONS **************/

    /**
     * @return void
     * @throws BadTypeException
     * @throws ModelNotFoundException
     */
    public function actionEdit(): void {
        $this->traitActionEdit();
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

        $form->addSubmit('stalk', _('Let\'s stalk'))
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
     * @return Control
     * @throws ModelNotFoundException
     */
    protected function createComponentCreateForm(): Control {
        return new PersonFormComponent($this->getContext(), true, $this->getUserPermissions(false));
    }

    /**
     * @return Control
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): Control {
        return new PersonFormComponent($this->getContext(), false, $this->getUserPermissions(true));
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
        return new StalkingContainer($this->getContext(), $this->getEntity(), $this->getUserPermissions(true));
    }

    /**
     * @param bool $throw
     * @return int
     * @throws ModelNotFoundException
     */
    private function getUserPermissions(bool $throw = true): int {
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

            } catch (ModelNotFoundException $exception) {
                if ($throw) {
                    throw $exception;
                }
                $this->userPermissions = FieldLevelPermission::ALLOW_FULL;
            }
        }
        return $this->userPermissions;
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
