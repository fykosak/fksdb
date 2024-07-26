<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Person\Detail\AddressComponent;
use FKSDB\Components\Controls\Person\Detail\Component;
use FKSDB\Components\Controls\Person\Detail\ContestantListComponent;
use FKSDB\Components\Controls\Person\Detail\EmailMessageListComponent;
use FKSDB\Components\Controls\Person\Detail\EventOrgListComponent;
use FKSDB\Components\Controls\Person\Detail\FlagComponent;
use FKSDB\Components\Controls\Person\Detail\FyziklaniTeamTeacherListComponent;
use FKSDB\Components\Controls\Person\Detail\HistoryListComponent;
use FKSDB\Components\Controls\Person\Detail\OrgListComponent;
use FKSDB\Components\Controls\Person\Detail\PaymentListComponent;
use FKSDB\Components\Controls\Person\Detail\RoleComponent;
use FKSDB\Components\Controls\Person\Detail\TaskContributionListComponent;
use FKSDB\Components\Controls\Person\Detail\Timeline\TimelineComponent;
use FKSDB\Components\Controls\Person\Detail\ValidationComponent;
use FKSDB\Components\Controls\Person\PizzaComponent;
use FKSDB\Components\EntityForms\AddressFormComponent;
use FKSDB\Components\EntityForms\PersonFormComponent;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Components\Controls\Stalking\StalkingContainer;
use FKSDB\Components\DataTest\PersonTestComponent;
use FKSDB\Components\DataTest\PersonTestGrid;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonSelectBox;
use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
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
    private PersonFactory $personFactory;
    private FieldLevelPermissionValue $userPermissions;

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
        return $this->contestAuthorizator->isAllowed(PersonModel::RESOURCE_ID, 'search', $this->getSelectedContest());
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
     * @throws NoContestAvailable
     * @throws NotFoundException
     * @throws NotFoundException
     * @throws NotFoundException
     */
    public function authorizedDetail(): bool
    {
        $full = $this->contestAuthorizator->isAllowed($this->getEntity(), 'detail.full', $this->getSelectedContest());
        $restrict = $this->contestAuthorizator->isAllowed(
            $this->getEntity(),
            'detail.restrict',
            $this->getSelectedContest()
        );
        $basic = $this->contestAuthorizator->isAllowed($this->getEntity(), 'detail.basic', $this->getSelectedContest());

        return $full || $restrict || $basic;
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

    /**
     * @throws NoContestAvailable
     */
    public function authorizedPizza(): bool
    {
        return $this->contestAuthorizator->isAllowed(PersonModel::RESOURCE_ID, 'pizza', $this->getSelectedContest());
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    final public function renderDetail(): void
    {
        $person = $this->getEntity();
        $this->template->isSelf = $this->getLoggedPerson()->person_id === $person->person_id;
        $this->template->userPermission = $this->getUserPermissions();
        Debugger::log(
            sprintf(
                '%s (%d) shows %s (%d)',
                $this->getLoggedPerson()->getFullName(),
                $this->getLoggedPerson()->person_id,
                $person->getFullName(),
                $person->person_id
            ),
            'person-detail-log'
        );
    }

    /* ******************* COMPONENTS *******************/
    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentEventOrgList(): EventOrgListComponent
    {
        return new EventOrgListComponent($this->getContext(), $this->getEntity(), $this->getUserPermissions(), true);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentFyziklaniTeacherList(): FyziklaniTeamTeacherListComponent
    {
        return new FyziklaniTeamTeacherListComponent(
            $this->getContext(),
            $this->getEntity(),
            $this->getUserPermissions(),
            false
        );
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentEventParticipantsGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'event_participant',
            $this->getEntity(),
            $this->getUserPermissions(),
            $this->getContext()
        );
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentTeamMembersGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'fyziklani_team_member',
            $this->getEntity(),
            $this->getUserPermissions(),
            $this->getContext()
        );
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentEventScheduleGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'schedule_item',
            $this->getEntity(),
            $this->getUserPermissions(),
            $this->getContext()
        );
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentDetailComponent(): Component
    {
        return new Component($this->getContext(), $this->getEntity(), $this->getUserPermissions(), true);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentAddresses(): AddressComponent
    {
        return new AddressComponent($this->getContext(), $this->getEntity(), $this->getUserPermissions(), true);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentPaymentList(): PaymentListComponent
    {
        return new PaymentListComponent($this->getContext(), $this->getEntity(), $this->getUserPermissions(), true);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentContestantList(): ContestantListComponent
    {
        return new ContestantListComponent($this->getContext(), $this->getEntity(), $this->getUserPermissions(), true);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentEmailMessageList(): EmailMessageListComponent
    {
        return new EmailMessageListComponent(
            $this->getContext(),
            $this->getEntity(),
            $this->getUserPermissions(),
            true
        );
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentOrgList(): OrgListComponent
    {
        return new OrgListComponent($this->getContext(), $this->getEntity(), $this->getUserPermissions(), true);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentHistoryList(): HistoryListComponent
    {
        return new HistoryListComponent($this->getContext(), $this->getEntity(), $this->getUserPermissions(), true);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentTaskContributionList(): TaskContributionListComponent
    {
        return new TaskContributionListComponent(
            $this->getContext(),
            $this->getEntity(),
            $this->getUserPermissions(),
            true
        );
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentRole(): RoleComponent
    {
        return new RoleComponent($this->getContext(), $this->getEntity(), $this->getUserPermissions(), true);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentFlag(): FlagComponent
    {
        return new FlagComponent($this->getContext(), $this->getEntity(), $this->getUserPermissions(), true);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentValidation(): ValidationComponent
    {
        return new ValidationComponent($this->getContext(), $this->getEntity(), $this->getUserPermissions(), true);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentTimeline(): TimelineComponent
    {
        return new TimelineComponent($this->getContext(), $this->getEntity());
    }

    protected function createComponentFormSearch(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addComponent(
            new PersonSelectBox(true, new PersonProvider($this->getContext()), _('Person')),
            'person_id'
        );

        $form->addSubmit('show', _('Show detail'))
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
        return $this->createComponentPostContactForm(PostContactType::Delivery);
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    protected function createComponentPermanentPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::Permanent);
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
    private function getUserPermissions(bool $throw = true): FieldLevelPermissionValue
    {
        if (!isset($this->userPermissions)) {
            $this->userPermissions = FieldLevelPermissionValue::Basic;
            try {
                $person = $this->getEntity();
                if ($this->contestAuthorizator->isAllowed($person, 'detail.basic', $this->getSelectedContest())) {
                    $this->userPermissions = FieldLevelPermission::ALLOW_BASIC;
                }
                if ($this->contestAuthorizator->isAllowed($person, 'detail.restrict', $this->getSelectedContest())) {
                    $this->userPermissions = FieldLevelPermission::ALLOW_RESTRICT;
                }
                if ($this->contestAuthorizator->isAllowed($person, 'detail.full', $this->getSelectedContest())) {
                    $this->userPermissions = FieldLevelPermission::ALLOW_FULL;
                }
            } catch (NotFoundException $exception) {
                if ($throw) {
                    throw $exception;
                }
                $this->userPermissions = FieldLevelPermissionValue::Full;
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
