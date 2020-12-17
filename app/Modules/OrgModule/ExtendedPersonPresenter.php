<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Config\Expressions\Helpers;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\IModel;
use FKSDB\Model\ORM\IService;
use FKSDB\Model\ORM\Models\ModelContestant;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\IControl;
use FKSDB\Model\Persons\AclResolver;
use FKSDB\Model\Persons\ExtendedPersonHandler;
use FKSDB\Model\Persons\ExtendedPersonHandlerFactory;
use FKSDB\Model\Persons\IExtendedPersonPresenter;

/**
 * Class ExtendedPersonPresenter
 *
 */
abstract class ExtendedPersonPresenter extends EntityPresenter implements IExtendedPersonPresenter {

    protected bool $sendEmail = true;

    private ReferencedPersonFactory $referencedPersonFactory;
    private ExtendedPersonHandlerFactory $handlerFactory;

    final public function injectExtendedPerson(ReferencedPersonFactory $referencedPersonFactory, ExtendedPersonHandlerFactory $handlerFactory): void {
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @param ModelContestant|IModel|null $model
     * @param Form|IControl[][] $form
     */
    protected function setDefaults(?IModel $model, Form $form): void {
        if (!$model) {
            return;
        }
        $form[ExtendedPersonHandler::CONT_AGGR][ExtendedPersonHandler::EL_PERSON]->setDefaultValue($model->person_id);
        if ($form->getComponent(ExtendedPersonHandler::CONT_MODEL, false)) {
            $form[ExtendedPersonHandler::CONT_MODEL]->setDefaults($this->getModel());
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    protected function getFieldsDefinition(): array {
        $contestId = $this->getSelectedContest()->contest_id;
        $contestName = $this->getContext()->getParameters()['contestMapping'][$contestId];
        return Helpers::evalExpressionArray($this->getContext()->getParameters()[$contestName][$this->fieldsDefinition], $this->getContext());
    }

    abstract protected function appendExtendedContainer(Form $form): void;

    abstract protected function getORMService(): IService;

    protected function getAcYearFromModel(): ?int {
        return null;
    }

    /**
     * @param bool $create
     * @return FormControl
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    private function createComponentFormControl(bool $create): FormControl {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $fieldsDefinition = $this->getFieldsDefinition();
        $acYear = $this->getAcYearFromModel() ? $this->getAcYearFromModel() : $this->getSelectedAcademicYear();
        $searchType = PersonSearchContainer::SEARCH_ID;
        $allowClear = $create;
        $modifiabilityResolver = $visibilityResolver = new AclResolver($this->contestAuthorizator, $this->getSelectedContest());
        $referencedId = $this->referencedPersonFactory->createReferencedPerson($fieldsDefinition, $acYear, $searchType, $allowClear, $modifiabilityResolver, $visibilityResolver);
        $referencedId->addRule(Form::FILLED, _('Person is required.'));
        $referencedId->getReferencedContainer()->setOption('label', _('Person'));

        $container->addComponent($referencedId, ExtendedPersonHandler::EL_PERSON);

        $this->appendExtendedContainer($form);

        $handler = $this->handlerFactory->create($this->getORMService(), $this->getSelectedContest(), $this->getSelectedYear(), $this->getContext()->getParameters()['invitation']['defaultLang']);

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
     * @return FormControl
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    final protected function createComponentCreateComponent(): FormControl {
        return $this->createComponentFormControl(true);
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    final protected function createComponentEditComponent(): FormControl {
        return $this->createComponentFormControl(false);
    }

    /**
     * @param int $id
     * @return AbstractModelSingle
     */
    protected function loadModel($id): ?IModel {
        return $this->getORMService()->findByPrimary($id);
    }
}