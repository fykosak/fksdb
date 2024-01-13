<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Person\PizzaComponent;
use FKSDB\Components\Controls\Stalking\StalkingContainer;
use FKSDB\Components\DataTest\PersonTestComponent;
use FKSDB\Components\DataTest\PersonTestGrid;
use FKSDB\Components\EntityForms\AddressFormComponent;
use FKSDB\Components\EntityForms\PersonFormComponent;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonSelectBox;
use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\Resource;
use Tracy\Debugger;

/**
 * Do not use this presenter to create/modify persons.
 *             It's better to use ReferencedId and ReferencedContainer
 *             inside the particular form.
 * TODO fix referenced person
 */
final class PersonPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<PersonModel> */
    use EntityPresenterTrait;

    private PersonService $personService;
    private int $userPermissions;

    final public function injectQuarterly(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    public function titleSearch(): PageTitle
    {
        return new PageTitle(null, _('Find person'), 'fas fa-search');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedSearch(): bool
    {
        return $this->contestAuthorizator->isAllowed('person', 'stalk.search', $this->getSelectedContest());
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Detail of person %s'), $this->getEntity()->getFullName()), 'fas fa-eye');
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    public function authorizedDetail(): bool
    {
        $full = $this->contestAuthorizator->isAllowed($this->getEntity(), 'stalk.full', $this->getSelectedContest());
        $restrict = $this->contestAuthorizator->isAllowed(
            $this->getEntity(),
            'stalk.restrict',
            $this->getSelectedContest()
        );
        $basic = $this->contestAuthorizator->isAllowed($this->getEntity(), 'stalk.basic', $this->getSelectedContest());

        return $full || $restrict || $basic;
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Edit person "%s"'), $this->getEntity()->getFullName()),
            'fas fa-user-edit'
        );
    }

    public function authorizedEdit(): bool
    {
        return $this->contestAuthorizator->isAllowed($this->getEntity(), 'edit', $this->getSelectedContest());
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create person'), 'fas fa-user-plus');
    }

    public function titlePizza(): PageTitle
    {
        return new PageTitle(null, _('Pizza'), 'fas fa-pizza-slice');
    }

    public function authorizedPizza(): bool
    {
        return $this->contestAuthorizator->isAllowed('person', 'pizza', $this->getSelectedContest());
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    final public function renderDetail(): void
    {
        $person = $this->getEntity();
        $this->template->isSelf = $this->getLoggedPerson()->person_id === $person->person_id;
        Debugger::log(
            sprintf(
                '%s (%d) stalk %s (%d)',
                $this->getLoggedPerson()->getFullName(),
                $this->getLoggedPerson()->person_id,
                $person->getFullName(),
                $person->person_id
            ),
            'stalking-log'
        );
    }

    public function authorizedTests(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            PersonModel::RESOURCE_ID,
            'data-test',
            $this->getSelectedContest()
        );
    }

    public function titleTests(): PageTitle
    {
        return new PageTitle(null, _('Test data'), 'fas fa-tasks');
    }

    public function authorizedList(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            PersonModel::RESOURCE_ID,
            'data-test',
            $this->getSelectedContest()
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Test data'), 'fas fa-tasks');
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    public function createComponentStalkingContainer(): StalkingContainer
    {
        return new StalkingContainer($this->getContext(), $this->getEntity(), $this->getUserPermissions());
    }

    protected function createComponentFormSearch(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addComponent(
            new PersonSelectBox(true, new PersonProvider($this->getContext()), _('Person')),
            'person_id'
        );

        $form->addSubmit('stalk', _('Let\'s stalk'))
            ->onClick[] =
            function (SubmitButton $button) {
                /** @phpstan-var array{person_id:int} $values */
                $values = $button->getForm()->getValues('array');
                $this->redirect('detail', ['id' => $values['person_id']]);
            };
        $form->addSubmit('edit', _('button.edit'))
            ->onClick[] =
            function (SubmitButton $button) {
                /** @phpstan-var array{person_id:int} $values */
                $values = $button->getForm()->getValues('array');
                $this->redirect('edit', ['id' => $values['person_id']]);
            };

        return $control;
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    protected function createComponentCreateForm(): PersonFormComponent
    {
        return new PersonFormComponent($this->getContext(), $this->getUserPermissions(false), null);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentDeliveryPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::from(PostContactType::DELIVERY));
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentPermanentPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::from(PostContactType::PERMANENT));
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    private function createComponentPostContactForm(PostContactType $type): AddressFormComponent
    {
        return new AddressFormComponent(
            $this->getContext(),
            $type,
            $this->getEntity()
        );
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    private function getUserPermissions(bool $throw = true): int
    {
        if (!isset($this->userPermissions)) {
            $this->userPermissions = FieldLevelPermission::ALLOW_ANYBODY;
            try {
                $person = $this->getEntity();
                if ($this->contestAuthorizator->isAllowed($person, 'stalk.basic', $this->getSelectedContest())) {
                    $this->userPermissions = FieldLevelPermission::ALLOW_BASIC;
                }
                if ($this->contestAuthorizator->isAllowed($person, 'stalk.restrict', $this->getSelectedContest())) {
                    $this->userPermissions = FieldLevelPermission::ALLOW_RESTRICT;
                }
                if ($this->contestAuthorizator->isAllowed($person, 'stalk.full', $this->getSelectedContest())) {
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
     * @throws GoneException
     * @throws NoContestAvailable
     */
    protected function createComponentEditForm(): PersonFormComponent
    {
        return new PersonFormComponent($this->getContext(), $this->getUserPermissions(), $this->getEntity());
    }

    protected function createComponentPizzaSelect(): PizzaComponent
    {
        return new PizzaComponent($this->getContext());
    }

    protected function createComponentDataTestGrid(): PersonTestGrid
    {
        return new PersonTestGrid($this->getContext());
    }

    protected function createComponentDataTestControl(): PersonTestComponent
    {
        return new PersonTestComponent($this->getContext());
    }

    /**
     * @return never
     * @throws NotImplementedException
     */
    protected function createComponentGrid(): BaseGrid
    {
        throw new NotImplementedException();
    }

    protected function getORMService(): PersonService
    {
        return $this->personService;
    }

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
