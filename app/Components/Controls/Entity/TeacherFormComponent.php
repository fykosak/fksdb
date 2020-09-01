<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\DBReflection\OmittedControlException;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTeacher;
use FKSDB\ORM\Services\ServiceTeacher;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Forms\Form;

/**
 * Class TeacherForm
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelTeacher $model
 */
class TeacherFormComponent extends EditEntityFormComponent {

    use ReferencedPersonTrait;

    private const CONTAINER = 'teacher';

    protected SchoolFactory $schoolFactory;

    private SingleReflectionFormFactory $singleReflectionFormFactory;

    protected ServiceTeacher $serviceTeacher;

    public function injectPrimary(SingleReflectionFormFactory $singleReflectionFormFactory, SchoolFactory $schoolFactory, ServiceTeacher $serviceTeacher): void {
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
    protected function configureForm(Form $form): void {
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
    protected function handleFormSuccess(Form $form): void {
        $data = FormUtils::emptyStrToNull($form->getValues()[self::CONTAINER], true);
        if ($this->create) {
            $this->serviceTeacher->createNewModel($data);
        } else {
            $this->serviceTeacher->updateModel2($this->model, $data);
        }
        $this->getPresenter()->flashMessage($this->create ? _('Teacher has been created') : _('Teacher has been updated'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @param AbstractModelSingle|null $model
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(?AbstractModelSingle $model): void {
        if (!is_null($model)) {
            $this->getForm()->setDefaults([self::CONTAINER => $model->toArray()]);
        }
    }

    /**
     * @return ModelContainer
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    private function createTeacherContainer(): ModelContainer {
        return $this->singleReflectionFormFactory->createContainer('teacher', ['state', 'since', 'until', 'number_brochures', 'note']);
    }
}
