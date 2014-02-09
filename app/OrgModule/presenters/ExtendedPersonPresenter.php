<?php

namespace OrgModule;

use FKS\Components\Controls\FormControl;
use FKS\Components\Forms\Containers\ContainerWithOptions;
use FKS\Config\Expressions\Helpers;
use FKSDB\Components\Forms\Factories\ReferencedPersonFactory;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use OrgModule\EntityPresenter;
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

    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    public function injectHandlerFactory(ExtendedPersonHandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
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
        return Helpers::evalExpressionArray($this->globalParameters[$contestName][$this->fieldsDefinition]);
    }

    abstract protected function appendExtendedContainer(Form $form);

    abstract protected function getORMService();

    /**
     * @note First '%s' is replaced with referenced person's name.
     */
    abstract public function messageCreate();

    /**
     * @note First '%s' is replaced with referenced person's name.
     */
    abstract public function messageEdit();

    abstract public function messageError();

    protected function getAcYearFromModel() {
        return null;
    }

    private function createComponentFormControl($create) {
        $control = new FormControl();
        $form = $control->getForm();
        $control->setGroupMode(FormControl::GROUP_CONTAINER);

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $fieldsDefinition = $this->getFieldsDefinition();
        $acYear = $this->getAcYearFromModel() ? $this->getAcYearFromModel() : $this->getSelectedAcademicYear();
        $searchType = ReferencedPersonFactory::SEARCH_ID;
        $allowClear = $create;
        $modifiabilityResolver = $visibilityResolver = new AclResolver($this->contestAuthorizator, $this->getSelectedContest(), $this->getModel() ? : 'contestant');
        $components = $this->referencedPersonFactory->createReferencedPerson($fieldsDefinition, $acYear, $searchType, $allowClear, $modifiabilityResolver, $visibilityResolver);
        $components[1]->setOption('label', _('Osoba'));

        $container->addComponent($components[0], ExtendedPersonHandler::EL_PERSON);
        $container->addComponent($components[1], ExtendedPersonHandler::CONT_PERSON);

        $this->appendExtendedContainer($form);

        $handler = $this->handlerFactory->create($this->getORMService(), $this->getSelectedContest(), $this->getSelectedYear(), $this->globalParameters['invitation']['defaultLang']);
        $submit = $form->addSubmit('send', $create ? _('Založit') : _('Uložit'));
        $that = $this;
        $submit->onClick[] = function(SubmitButton $button) use($that, $handler) {
                    $form = $button->getForm();
                    if ($handler->handleForm($form, $that)) {
                        $that->backlinkRedirect();
                        $that->redirect('list');
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

