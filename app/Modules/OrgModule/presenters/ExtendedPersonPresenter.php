<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Config\Expressions\Helpers;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelContestant;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\IControl;
use Nette\Utils\JsonException;
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

    /**
     * @var ReferencedPersonFactory
     */
    private $referencedPersonFactory;

    /**
     * @var ExtendedPersonHandlerFactory
     */
    private $handlerFactory;

    /**
     * @param ReferencedPersonFactory $referencedPersonFactory
     * @return void
     */
    public function injectReferencedPersonFactory(ReferencedPersonFactory $referencedPersonFactory) {
        $this->referencedPersonFactory = $referencedPersonFactory;
    }

    /**
     * @param ExtendedPersonHandlerFactory $handlerFactory
     * @return void
     */
    public function injectHandlerFactory(ExtendedPersonHandlerFactory $handlerFactory) {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @param ModelContestant|IModel|null $model
     * @param Form|IControl[][] $form
     */
    protected function setDefaults(IModel $model = null, Form $form) {
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
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    protected function getFieldsDefinition() {
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
     * @param bool $create
     * @return FormControl
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws OmittedControlException
     * @throws BadRequestException
     *
     */
    private function createComponentFormControl(bool $create): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $container = new ContainerWithOptions();
        $form->addComponent($container, ExtendedPersonHandler::CONT_AGGR);

        $component = $this->referencedPersonFactory->createReferencedPerson(
            $this->getFieldsDefinition(),
            $this->getAcYearFromModel() ?: $this->getSelectedAcademicYear(),
            ReferencedPersonFactory::SEARCH_ID,
            $create,
            new AclResolver($this->contestAuthorizator, $this->getSelectedContest()),
            new AclResolver($this->contestAuthorizator, $this->getSelectedContest())
        );
        $component->getReferencedId()->addRule(Form::FILLED, _('Osobu je třeba zadat.'));
        $component->setOption('label', _('Person'));

        $container->addComponent($component->getReferencedId(), ExtendedPersonHandler::EL_PERSON);
        $container->addComponent($component, ExtendedPersonHandler::CONT_PERSON);

        $this->appendExtendedContainer($form);

        $handler = $this->handlerFactory->create($this->getORMService(), $this->getSelectedContest(), $this->getSelectedYear(), $this->globalParameters['invitation']['defaultLang']);

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
     *
     * @throws AbstractColumnException
     *
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws JsonException
     * @throws NotImplementedException
     * @throws OmittedControlException
     * @throws BadRequestException
     *
     */
    final protected function createComponentCreateComponent(): FormControl {
        return $this->createComponentFormControl(true);
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     * @throws JsonException
     * @throws AbstractColumnException
     * @throws OmittedControlException
     * @throws NotImplementedException
     * @throws ForbiddenRequestException
     * @throws BadRequestException
     */
    final protected function createComponentEditComponent(): FormControl {
        return $this->createComponentFormControl(false);
    }

    /**
     * @param int $id
     * @return AbstractModelSingle
     */
    protected function loadModel($id) {
        return $this->getORMService()->findByPrimary($id);
    }
}
