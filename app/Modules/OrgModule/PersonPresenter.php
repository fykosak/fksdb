<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

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
use FKSDB\Components\Grids\Components\Grid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\Utils\UI\PageTitle;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\Resource;
use Tracy\Debugger;

/**
 * Do not use this presenter to create/modify persons.
 *             It's better to use ReferencedId and ReferencedContainer
 *             inside the particular form.
 * TODO fix referenced person
 * @method PersonModel getEntity()
 */
class PersonPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private PersonService $personService;
    private PersonFactory $personFactory;
    private FieldLevelPermissionValue $userPermissions;

    final public function injectQuarterly(
        PersonService $personService,
        PersonFactory $personFactory
    ): void {
        $this->personService = $personService;
        $this->personFactory = $personFactory;
    }

    /* *********** TITLE ***************/
    public function titleSearch(): PageTitle
    {
        return new PageTitle(null, _('Find person'), 'fa fa-search');
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Detail of person %s'), $this->getEntity()->getFullName()), 'fa fa-eye');
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
            'fa fa-user-edit'
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create person'), 'fa fa-user-plus');
    }

    public function titlePizza(): PageTitle
    {
        return new PageTitle(null, _('Pizza'), 'fa fa-pizza-slice');
    }

    /* *********** AUTH ***************/
    public function authorizedSearch(): void
    {
        $this->setAuthorized($this->isAnyContestAuthorized('person', 'detail.search'));
    }

    public function authorizedEdit(): void
    {
        $this->setAuthorized($this->isAnyContestAuthorized($this->getEntity(), 'edit'));
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function authorizedDetail(): void
    {
        $full = $this->isAnyContestAuthorized($this->getEntity(), 'detail.full');
        $restrict = $this->isAnyContestAuthorized($this->getEntity(), 'detail.restrict');
        $basic = $this->isAnyContestAuthorized($this->getEntity(), 'detail.basic');

        $this->setAuthorized($full || $restrict || $basic);
    }

    /* ********************* ACTIONS **************/

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
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

    /**
     * @throws BadTypeException
     */
    protected function createComponentFormSearch(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addComponent(
            $this->personFactory->createPersonSelect(true, _('Person'), new PersonProvider($this->personService)),
            'person_id'
        );

        $form->addSubmit('show', _('Show detail'))
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
     * @throws GoneException
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
        return $this->createComponentPostContactForm(PostContactType::Delivery);
    }

    /**
     * @throws GoneException
     * @throws ModelNotFoundException
     */
    protected function createComponentPermanentPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::Permanent);
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
     */
    private function getUserPermissions(bool $throw = true): FieldLevelPermissionValue
    {
        if (!isset($this->userPermissions)) {
            $this->userPermissions = FieldLevelPermissionValue::Basic;
            try {
                $person = $this->getEntity();
                if ($this->isAnyContestAuthorized($person, 'detail.basic')) {
                    $this->userPermissions = FieldLevelPermissionValue::Basic;
                }
                if ($this->isAnyContestAuthorized($person, 'detail.restrict')) {
                    $this->userPermissions = FieldLevelPermissionValue::Restrict;
                }
                if ($this->isAnyContestAuthorized($person, 'detail.full')) {
                    $this->userPermissions = FieldLevelPermissionValue::Full;
                }
            } catch (ModelNotFoundException $exception) {
                if ($throw) {
                    throw $exception;
                }
                $this->userPermissions = FieldLevelPermissionValue::Full;
            }
        }
        return $this->userPermissions;
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
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
    protected function createComponentGrid(): Grid
    {
        throw new NotImplementedException();
    }

    protected function getORMService(): PersonService
    {
        return $this->personService;
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
