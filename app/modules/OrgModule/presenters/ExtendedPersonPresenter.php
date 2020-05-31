<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Config\Expressions\Helpers;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\IModel;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Persons\AclResolver;
use Persons\ExtendedPersonHandler;
use Persons\ExtendedPersonHandlerFactory;
use Persons\IExtendedPersonPresenter;

/**
 * Class ExtendedPersonPresenter
 * *
 */
abstract class ExtendedPersonPresenter extends EntityPresenter implements IExtendedPersonPresenter {
    /**
     * @var bool
     */
    protected $sendEmail = true;

    private ReferencedPersonFactory $referencedPersonFactory;

    private ExtendedPersonHandlerFactory $handlerFactory;

    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory): void {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    public function injectHandlerFactory(ExtendedPersonHandlerFactory $handlerFactory): void {
        $this->handlerFactory = $handlerFactory;
    }

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
     * @throws BadRequestException
     */
    private function getFieldsDefinition() {
        $contestId = $this->getSelectedContest()->contest_id;
        $contestName = $this->globalParameters['contestMapping'][$contestId];
        return Helpers::evalExpressionArray($this->globalParameters[$contestName][$this->fieldsDefinition], $this->getContext());
    }

    /**
     * @param Form $form
     * @return void
     */
    abstract protected function appendExtendedContainer(Form $form);

    /**
     * @return AbstractServiceMulti|AbstractServiceSingle
     */
    abstract protected function getORMService();

    /**
     * @return null
     */
    protected function getAcYearFromModel() {
        return null;
    }

    /**
     * @param $create
     * @return FormControl
     * @throws BadRequestException
     * @throws \Exception
     */
    private function createComponentFormControl($create) {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $fieldsDefinition = $this->getFieldsDefinition();
        $acYear = $this->getAcYearFromModel() ? $this->getAcYearFromModel() : $this->getSelectedAcademicYear();
        $searchType = ReferencedPersonFactory::SEARCH_ID;
        $allowClear = $create;
        $modifiabilityResolver = $visibilityResolver = new AclResolver($this->contestAuthorizator, $this->getSelectedContest());
        $components = $this->referencedPersonFactory->createReferencedPerson($fieldsDefinition, $acYear, $searchType, $allowClear, $modifiabilityResolver, $visibilityResolver);
        $components[0]->addRule(Form::FILLED, _('Osobu je třeba zadat.'));
        $components[1]->setOption('label', _('Osoba'));

        $container->addComponent($components[0], ExtendedPersonHandler::EL_PERSON);
        $container->addComponent($components[1], ExtendedPersonHandler::CONT_PERSON);

        $this->appendExtendedContainer($form);

        $handler = $this->handlerFactory->create($this->getORMService(), $this->getSelectedContest(), $this->getSelectedYear(), $this->globalParameters['invitation']['defaultLang']);
        $submit = $form->addSubmit('send', $create ? _('Založit') : _('Save'));

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
     */
    final protected function createComponentCreateComponent() {
        return $this->createComponentFormControl(true);
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    final protected function createComponentEditComponent() {
        return $this->createComponentFormControl(false);
    }

    /**
     * @param $id
     * @return AbstractModelSingle
     */
    protected function loadModel($id) {
        return $this->getORMService()->findByPrimary($id);
    }
}
