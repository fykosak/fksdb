<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\ModelTeacher;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\ServiceTeacher;
use FKSDB\Models\Utils\FormUtils;
use Nette\Forms\Form;

/**
 * @property ModelTeacher|null $model
 */
class TeacherFormComponent extends AbstractEntityFormComponent
{
    use ReferencedPersonTrait;

    private const CONTAINER = 'teacher';
    private SchoolFactory $schoolFactory;
    private SingleReflectionFormFactory $singleReflectionFormFactory;
    private ServiceTeacher $serviceTeacher;

    final public function injectPrimary(
        SingleReflectionFormFactory $singleReflectionFormFactory,
        SchoolFactory $schoolFactory,
        ServiceTeacher $serviceTeacher
    ): void {
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->schoolFactory = $schoolFactory;
        $this->serviceTeacher = $serviceTeacher;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $container = $this->createTeacherContainer();
        $schoolContainer = $this->schoolFactory->createSchoolSelect();
        $container->addComponent($schoolContainer, 'school_id');
        $personInput = $this->createPersonSelect();
        if (!$this->isCreating()) {
            $personInput->setDisabled();
        }
        $container->addComponent($personInput, 'person_id', 'state');
        $form->addComponent($container, self::CONTAINER);
    }

    protected function handleFormSuccess(Form $form): void
    {
        $data = FormUtils::emptyStrToNull($form->getValues()[self::CONTAINER], true);
        $this->serviceTeacher->storeModel($data, $this->model);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Teacher has been updated') : _('Teacher has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void
    {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    private function createTeacherContainer(): ModelContainer
    {
        return $this->singleReflectionFormFactory->createContainer(
            'teacher',
            ['state', 'since', 'until', 'number_brochures', 'note']
        );
    }
}
