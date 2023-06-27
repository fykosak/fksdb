<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\TeacherModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\TeacherService;
use FKSDB\Models\Persons\Resolvers\AclResolver;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @property TeacherModel|null $model
 */
class TeacherFormComponent extends EntityFormComponent
{
    use ReferencedPersonTrait;

    private const CONTAINER = 'teacher';
    private SchoolFactory $schoolFactory;
    private SingleReflectionFormFactory $singleReflectionFormFactory;
    private TeacherService $teacherService;
    private ContestYearModel $contestYear;
    private ContestAuthorizator $contestAuthorizator;

    public function __construct(Container $container, ContestYearModel $contestYear, ?Model $model)
    {
        parent::__construct($container, $model);
        $this->contestYear = $contestYear;
    }

    final public function injectPrimary(
        SingleReflectionFormFactory $singleReflectionFormFactory,
        SchoolFactory $schoolFactory,
        TeacherService $teacherService,
        ContestAuthorizator $contestAuthorizator
    ): void {
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->schoolFactory = $schoolFactory;
        $this->teacherService = $teacherService;
        $this->contestAuthorizator = $contestAuthorizator;
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
        $referencedId = $this->createPersonId(
            $this->contestYear,
            isset($this->model),
            new AclResolver($this->contestAuthorizator, $this->contestYear->contest),
            $this->getContext()->getParameters()['forms']['adminTeacher']
        );
        $container->addComponent($referencedId, 'person_id', 'state');
        $form->addComponent($container, self::CONTAINER);
    }

    protected function handleFormSuccess(Form $form): void
    {
        $data = FormUtils::emptyStrToNull2($form->getValues()[self::CONTAINER]);
        $this->teacherService->storeModel($data, $this->model);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Teacher has been updated') : _('Teacher has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    private function createTeacherContainer(): ModelContainer
    {
        return $this->singleReflectionFormFactory->createContainerWithMetadata(
            'teacher',
            [
                'state' => ['required' => true],
                'since' => ['required' => true],
                'until' => ['required' => true],
                'number_brochures' => ['required' => true],
                'note' => ['required' => true],
            ]
        );
    }
}
