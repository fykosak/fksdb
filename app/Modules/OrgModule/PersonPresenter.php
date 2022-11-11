<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Person\PizzaComponent;
use FKSDB\Components\Controls\Stalking\StalkingContainer;
use FKSDB\Components\EntityForms\AddressFormComponent;
use FKSDB\Components\EntityForms\PersonFormComponent;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\BaseGrid;
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
        $this->setAuthorized($this->isAnyContestAuthorized('person', 'stalk.search'));
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
        $full = $this->isAnyContestAuthorized($this->getEntity(), 'stalk.full');
        $restrict = $this->isAnyContestAuthorized($this->getEntity(), 'stalk.restrict');
        $basic = $this->isAnyContestAuthorized($this->getEntity(), 'stalk.basic');

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

    /* ******************* COMPONENTS *******************/

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
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
            $this->personFactory->createPersonSelect(true, _('Person'), new PersonProvider($this->personService)),
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
                if ($this->isAnyContestAuthorized($person, 'stalk.basic')) {
                    $this->userPermissions = FieldLevelPermissionValue::Basic;
                }
                if ($this->isAnyContestAuthorized($person, 'stalk.restrict')) {
                    $this->userPermissions = FieldLevelPermissionValue::Restrict;
                }
                if ($this->isAnyContestAuthorized($person, 'stalk.full')) {
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
    protected function createComponentGrid(): BaseGrid
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
