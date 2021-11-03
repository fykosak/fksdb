<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Person\PizzaComponent;
use FKSDB\Components\Controls\Stalking\StalkingContainer;
use FKSDB\Components\EntityForms\PersonFormComponent;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServicePerson;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\Resource;
use Tracy\Debugger;

/**
 * Do not use this presenter to create/modify persons.
 *             It's better to use ReferencedId and ReferencedContainer
 *             inside the particular form.
 * TODO fix referenced person
 * @method ModelPerson getEntity()
 */
class PersonPresenter extends BasePresenter
{
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
    public function titleSearch(): PageTitle
    {
        return new PageTitle(_('Find person'), 'fa fa-search');
    }

    /**
     * @throws ModelNotFoundException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(sprintf(_('Detail of person %s'), $this->getEntity()->getFullName()), 'fa fa-eye');
    }

    /**
     * @throws ModelNotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(sprintf(_('Edit person "%s"'), $this->getEntity()->getFullName()), 'fa fa-user-edit');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('Create person'), 'fa fa-user-plus');
    }

    public function titlePizza(): PageTitle
    {
        return new PageTitle(_('Pizza'), 'fa fa-pizza-slice');
    }

    /* *********** AUTH ***************/
    public function authorizedSearch(): void
    {
        $this->setAuthorized($this->isAnyContestAuthorized('person', 'stalk.search'));
    }

    public function authorizedEdit(): void
    {
        $this->setAuthorized($this->isAnyContestAuthorized($this->getEntity(), 'edit'));
    }

    /**
     * @throws ModelNotFoundException
     */
    public function authorizedDetail(): void
    {
        $full = $this->isAnyContestAuthorized($this->getEntity(), 'stalk.full');
        $restrict = $this->isAnyContestAuthorized($this->getEntity(), 'stalk.restrict');
        $basic = $this->isAnyContestAuthorized($this->getEntity(), 'stalk.basic');

        $this->setAuthorized($full || $restrict || $basic);
    }

    /* ********************* ACTIONS **************/

    /**
     * @throws ModelNotFoundException
     */
    final public function renderDetail(): void
    {
        $person = $this->getEntity();
        $this->template->isSelf = $this->getUser()->getIdentity()->getPerson()->person_id === $person->person_id;
        /** @var ModelPerson $userPerson */
        $userPerson = $this->getUser()->getIdentity()->getPerson();
        Debugger::log(
            sprintf(
                '%s (%d) stalk %s (%d)',
                $userPerson->getFullName(),
                $userPerson->person_id,
                $person->getFullName(),
                $person->person_id
            ),
            'stalking-log'
        );
    }

    /* ******************* COMPONENTS *******************/

    /**
     * @throws ModelNotFoundException
     */
    public function createComponentStalkingContainer(): StalkingContainer
    {
        return new StalkingContainer($this->getContext(), $this->getEntity(), $this->getUserPermissions());
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentFormSearch(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addComponent(
            $this->personFactory->createPersonSelect(true, _('Person'), new PersonProvider($this->servicePerson)),
            'person_id'
        );

        $form->addSubmit('stalk', _('Let\'s stalk'))
            ->onClick[] =
            function (SubmitButton $button) {
                $values = $button->getForm()->getValues();
                $this->redirect('detail', ['id' => $values['person_id']]);
            };
        $form->addSubmit('edit', _('Edit'))
            ->onClick[] =
            function (SubmitButton $button) {
                $values = $button->getForm()->getValues();
                $this->redirect('edit', ['id' => $values['person_id']]);
            };

        return $control;
    }

    /**
     * @throws ModelNotFoundException
     */
    protected function createComponentCreateForm(): PersonFormComponent
    {
        return new PersonFormComponent($this->getContext(), $this->getUserPermissions(false), null);
    }

    /**
     * @throws ModelNotFoundException
     */
    private function getUserPermissions(bool $throw = true): int
    {
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

    /**
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): PersonFormComponent
    {
        return new PersonFormComponent($this->getContext(), $this->getUserPermissions(), $this->getEntity());
    }

    protected function createComponentPizzaSelect(): PizzaComponent
    {
        return new PizzaComponent($this->getContext());
    }

    /**
     * @throws NotImplementedException
     */
    protected function createComponentGrid(): BaseGrid
    {
        throw new NotImplementedException();
    }

    protected function getORMService(): ServicePerson
    {
        return $this->servicePerson;
    }

    /**
     * @param Resource|string $resource
     * all auth method is overwritten
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAnyContestAuthorized($resource, $privilege);
    }
}
