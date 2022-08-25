<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Expressions\Helpers;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\Persons\Resolvers\AclResolver;
use FKSDB\Models\Persons\ExtendedPersonHandler;
use FKSDB\Models\Persons\ExtendedPersonHandlerFactory;
use FKSDB\Models\Persons\ExtendedPersonPresenter as IExtendedPersonPresenter;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Service;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Control;
use Nette\Forms\Controls\SubmitButton;

abstract class ExtendedPersonPresenter extends EntityPresenter implements IExtendedPersonPresenter
{

    protected bool $sendEmail = true;
    private ReferencedPersonFactory $referencedPersonFactory;
    private ExtendedPersonHandlerFactory $handlerFactory;

    final public function injectExtendedPerson(
        ReferencedPersonFactory $referencedPersonFactory,
        ExtendedPersonHandlerFactory $handlerFactory
    ): void {
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @param ContestantModel|null $model
     * @param Form|Control[][] $form
     */
    protected function setDefaults(?Model $model, Form $form): void
    {
        if (!$model) {
            return;
        }
        $form[ExtendedPersonHandler::CONT_AGGR][ExtendedPersonHandler::EL_PERSON]->setDefaultValue($model->person_id);
        if ($form->getComponent(ExtendedPersonHandler::CONT_MODEL, false)) {
            $form[ExtendedPersonHandler::CONT_MODEL]->setDefaults($this->getModel());
        }
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    final protected function createComponentCreateComponent(): FormControl
    {
        return $this->createComponentFormControl(true);
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    private function createComponentFormControl(bool $create): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $referencedId = $this->referencedPersonFactory->createReferencedPerson(
            $this->getFieldsDefinition(),
            $this->getAcYearFromModel() ?? $this->getSelectedContestYear(),
            PersonSearchContainer::SEARCH_ID,
            $create,
            new AclResolver($this->contestAuthorizator, $this->getSelectedContest()),
        );
        $referencedId->addRule(Form::FILLED, _('Person is required.'));
        $referencedId->getReferencedContainer()->setOption('label', _('Person'));

        $container->addComponent($referencedId, ExtendedPersonHandler::EL_PERSON);

        $this->appendExtendedContainer($form);

        $handler = $this->handlerFactory->create(
            $this->getORMService(),
            $this->getSelectedContestYear(),
            $this->getContext()->getParameters()['invitation']['defaultLang']
        );

        $submit = $form->addSubmit('send', $create ? _('Create') : _('Save'));

        $submit->onClick[] = function (SubmitButton $button) use ($handler) {
            $form = $button->getForm();
            if ($handler->handleForm($form, $this, $this->sendEmail)) {
                $this->backLinkRedirect();
                $this->redirect('list');
            }
        };
        return $control;
    }

    /**
     * @throws \ReflectionException
     */
    protected function getFieldsDefinition(): array
    {
        $contestName = $this->getSelectedContest()->getContestSymbol();
        return Helpers::evalExpressionArray(
            $this->getContext()->getParameters()[$contestName][$this->fieldsDefinition],
            $this->getContext()
        );
    }

    protected function getAcYearFromModel(): ?ContestYearModel
    {
        return null;
    }

    abstract protected function appendExtendedContainer(Form $form): void;

    abstract protected function getORMService(): Service;

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    final protected function createComponentEditComponent(): FormControl
    {
        return $this->createComponentFormControl(false);
    }

    protected function loadModel(int $id): ?Model
    {
        return $this->getORMService()->findByPrimary($id);
    }
}
