<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKS\Config\Expressions\Helpers;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use ORM\IModel;
use Persons\AclResolver;
use Persons\ExtendedPersonHandler;
use Persons\ExtendedPersonHandlerFactory;
use Persons\IExtendedPersonPresenter;

abstract class ExtendedPersonPresenter extends EntityPresenter implements IExtendedPersonPresenter {

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

    /**
     * @var ExtendedPersonHandlerFactory
     */
    private $handlerFactory;

    /**
     * @var Container
     */
    private $container;

    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    public function injectHandlerFactory(ExtendedPersonHandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
    }

    public function injectContainer(Container $container) {
        $this->container = $container;
    }

    protected function setDefaults(IModel $model = null, Form $form) {
        if (!$model) {
            return;
        }
        $form[ExtendedPersonHandler::CONT_AGGR][ExtendedPersonHandler::EL_PERSON]->setDefaultValue($model->person_id);
        if ($form->getComponent(ExtendedPersonHandler::CONT_MODEL, false)) {
            $form[ExtendedPersonHandler::CONT_MODEL]->setDefaults($this->getModel());
        }
    }

    private function getFieldsDefinition() {
        $contestId = $this->getSelectedContest()->contest_id;
        $contestName = $this->globalParameters['contestMapping'][$contestId];
        return Helpers::evalExpressionArray($this->globalParameters[$contestName][$this->fieldsDefinition], $this->container);
    }

    abstract protected function appendExtendedContainer(Form $form);

    abstract protected function getORMService();

    protected function getAcYearFromModel() {
        return null;
    }

    private function createComponentFormControl($create) {
        $control = new FormControl();
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
        $submit = $form->addSubmit('send', $create ? _('Založit') : _('Uložit'));

        $submit->onClick[] = function (SubmitButton $button) use ($handler) {
            $form = $button->getForm();
            if ($handler->handleForm($form, $this)) {
                $this->backlinkRedirect();
                $this->redirect('list');
            }
        };
        return $control;
    }

    protected final function createComponentCreateComponent($name) {
        $control = $this->createComponentFormControl(true);
        return $control;
    }

    protected final function createComponentEditComponent($name) {
        $control = $this->createComponentFormControl(false);
        return $control;
    }

    protected function loadModel($id) {
        return $this->getORMService()->findByPrimary($id);
    }

}

