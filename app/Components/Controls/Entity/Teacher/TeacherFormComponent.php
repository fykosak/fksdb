<?php

namespace FKSDB\Components\Controls\Entity\Teacher;

use FKSDB\Components\Controls\Entity\AbstractEntityFormComponent;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Components\Controls\Entity\ReferencedPersonTrait;
use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTeacher;
use FKSDB\ORM\Services\ServiceTeacher;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * Class TeacherForm
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeacherFormComponent extends AbstractEntityFormComponent implements IEditEntityForm {

    use ReferencedPersonTrait;

    const CONTAINER = 'teacher';

    /** @var SchoolFactory */
    protected $schoolFactory;

    /** @var SingleReflectionFormFactory */
    private $singleReflectionFormFactory;

    /** @var ServiceTeacher */
    protected $serviceTeacher;

    /** @var ModelTeacher */
    private $model;

    /**
     * @param SingleReflectionFormFactory $singleReflectionFormFactory
     * @param SchoolFactory $schoolFactory
     * @param ServiceTeacher $serviceTeacher
     * @return void
     */
    public function injectPrimary(SingleReflectionFormFactory $singleReflectionFormFactory, SchoolFactory $schoolFactory, ServiceTeacher $serviceTeacher) {
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->schoolFactory = $schoolFactory;
        $this->serviceTeacher = $serviceTeacher;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form) {
        $container = $this->createTeacherContainer();
        $schoolContainer = $this->schoolFactory->createSchoolSelect();
        $container->addComponent($schoolContainer, 'school_id');
        $personInput = $this->createPersonSelect();
        if (!$this->create) {
            $personInput->setDisabled();
        }
        $container->addComponent($personInput, 'person_id', 'state');
        $form->addComponent($container, self::CONTAINER);
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form) {
        $data = FormUtils::emptyStrToNull($form->getValues()[self::CONTAINER], true);
        try {
            if ($this->create) {
                $this->getORMService()->createNewModel($data);
            } else {
                $this->getORMService()->updateModel2($this->model, $data);
            }
            $this->getPresenter()->flashMessage($this->create ? _('Teacher has been created') : _('Teacher has been updated'), Message::LVL_SUCCESS);
            $this->getPresenter()->redirect('list');
        } catch (ModelException $exception) {
            Debugger::log($exception);
            $this->flashMessage(_('Error'), Message::LVL_DANGER);
        }
    }

    /**
     * @param AbstractModelSingle $model
     * @return void
     * @throws BadTypeException
     */
    public function setModel(AbstractModelSingle $model) {
        $this->model = $model;
        $this->getForm()->setDefaults([self::CONTAINER => $model->toArray()]);
    }

    protected function getORMService(): ServiceTeacher {
        return $this->serviceTeacher;
    }

    /**
     * @return ModelContainer
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    public function createTeacherContainer(): ModelContainer {
        return $this->singleReflectionFormFactory->createContainer('teacher', ['state', 'since', 'until', 'number_brochures', 'note']);
    }
}
