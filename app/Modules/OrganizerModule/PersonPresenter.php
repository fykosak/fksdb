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
use FKSDB\Models\Authorization\Resource\PseudoContestResource;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Forms\Controls\SubmitButton;
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


    /**
     * @throws NoContestAvailable
     */
    public function authorizedSearch(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            new PseudoContestResource(PersonModel::RESOURCE_ID, $this->getSelectedContest()),
            'search',
            $this->getSelectedContest()
        );
    }

    public function titleSearch(): PageTitle
    {
        return new PageTitle(null, _('Find person'), 'fas fa-search');
    }

    /**
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NotFoundException
     * @throws NotFoundException
     * @throws NotFoundException
     */
    public function authorizedDetail(): bool
    {
        $full = $this->contestAuthorizator->isAllowed(
            new PseudoContestResource($this->getEntity(), $this->getSelectedContest()),
            'detail.full',
            $this->getSelectedContest()
        );
        $restrict = $this->contestAuthorizator->isAllowed(
            new PseudoContestResource($this->getEntity(), $this->getSelectedContest()),
            'detail.restrict',
            $this->getSelectedContest()
        );
        $basic = $this->contestAuthorizator->isAllowed(
            new PseudoContestResource($this->getEntity(), $this->getSelectedContest()),
            'detail.basic',
            $this->getSelectedContest()
        );

        return $full || $restrict || $basic;
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Detail of person %s'), $this->getEntity()->getFullName()), 'fas fa-eye');
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
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

    /**
     * @throws NotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    public function authorizedEdit(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            new PseudoContestResource($this->getEntity(), $this->getSelectedContest()),
            'edit',
            $this->getSelectedContest()
        );
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Edit person "%s"'), $this->getEntity()->getFullName()),
            'fas fa-user-edit'
        );
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            new PseudoContestResource(PersonModel::RESOURCE_ID, $this->getSelectedContest()),
            'create',
            $this->getSelectedContest()
        );
    }
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create person'), 'fas fa-user-plus');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedPizza(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            new PseudoContestResource(PersonModel::RESOURCE_ID, $this->getSelectedContest()),
            'pizza',
            $this->getSelectedContest()
        );
    }
    public function titlePizza(): PageTitle
    {
        return new PageTitle(null, _('Pizza'), 'fas fa-pizza-slice');
    }


    /**
     * @throws NoContestAvailable
     */
    public function authorizedTests(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            new PseudoContestResource(PersonModel::RESOURCE_ID, $this->getSelectedContest()),
            'data-test',
            $this->getSelectedContest()
        );
    }

    public function titleTests(): PageTitle
    {
        return new PageTitle(null, _('Test data'), 'fas fa-tasks');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            new PseudoContestResource(PersonModel::RESOURCE_ID, $this->getSelectedContest()),
            'data-test',
            $this->getSelectedContest()
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Test data'), 'fas fa-tasks');
    }

    /**
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NotFoundException
     * @throws NotFoundException
     */
    public function createComponentDetailContainer(): StalkingContainer
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

        $form->addSubmit('detail', _('Let\'s stalk'))
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
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NotFoundException
     */
    protected function createComponentCreateForm(): PersonFormComponent
    {
        return new PersonFormComponent($this->getContext(), $this->getUserPermissions(false), null);
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    protected function createComponentDeliveryPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::from(PostContactType::DELIVERY));
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    protected function createComponentPermanentPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::from(PostContactType::PERMANENT));
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
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
     * @throws NotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    private function getUserPermissions(bool $throw = true): int
    {
        if (!isset($this->userPermissions)) {
            $this->userPermissions = FieldLevelPermission::ALLOW_ANYBODY;
            try {
                if (
                    $this->contestAuthorizator->isAllowed(
                        new PseudoContestResource($this->getEntity(), $this->getSelectedContest()),
                        'detail.basic',
                        $this->getSelectedContest()
                    )
                ) {
                    $this->userPermissions = FieldLevelPermission::ALLOW_BASIC;
                }
                if (
                    $this->contestAuthorizator->isAllowed(
                        new PseudoContestResource($this->getEntity(), $this->getSelectedContest()),
                        'detail.restrict',
                        $this->getSelectedContest()
                    )
                ) {
                    $this->userPermissions = FieldLevelPermission::ALLOW_RESTRICT;
                }
                if (
                    $this->contestAuthorizator->isAllowed(
                        new PseudoContestResource($this->getEntity(), $this->getSelectedContest()),
                        'detail.full',
                        $this->getSelectedContest()
                    )
                ) {
                    $this->userPermissions = FieldLevelPermission::ALLOW_FULL;
                }
            } catch (NotFoundException $exception) {
                if ($throw) {
                    throw $exception;
                }
                $this->userPermissions = FieldLevelPermission::ALLOW_FULL;
            }
        }
        return $this->userPermissions;
    }

    /**
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NotFoundException
     * @throws NotFoundException
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

    protected function getORMService(): PersonService
    {
        return $this->personService;
    }
}
