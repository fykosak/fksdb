<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\SchoolSelectField;
use FKSDB\Models\Authorization\Authorizators\ContestAuthorizator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\TeacherModel;
use FKSDB\Models\ORM\Services\TeacherService;
use FKSDB\Models\Persons\Resolvers\AclResolver;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<TeacherModel>
 */
class TeacherFormComponent extends EntityFormComponent
{
    use ReferencedPersonTrait;

    private const CONTAINER = 'teacher';

    private TeacherService $teacherService;
    private ContestYearModel $contestYear;
    private ContestAuthorizator $contestAuthorizator;
    private LinkGenerator $linkGenerator;

    public function __construct(Container $container, ContestYearModel $contestYear, ?Model $model)
    {
        parent::__construct($container, $model);
        $this->contestYear = $contestYear;
    }

    final public function injectPrimary(
        TeacherService $teacherService,
        ContestAuthorizator $contestAuthorizator,
        LinkGenerator $linkGenerator
    ): void {
        $this->teacherService = $teacherService;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws InvalidLinkException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $container = $this->createTeacherContainer();
        $schoolContainer = new SchoolSelectField($this->container, $this->linkGenerator);
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

    protected function handleSuccess(Form $form): void
    {
        /**
         * @phpstan-var array{teacher:array{
         *   active:bool,
         *   role:string,
         *   note:string,
         * }} $values
         */
        $values = $form->getValues('array');
        $data = FormUtils::emptyStrToNull2($values[self::CONTAINER]);
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
     * @throws ForbiddenRequestException
     */
    private function createTeacherContainer(): ContainerWithOptions
    {
        $container = new ModelContainer($this->container, 'teacher');
        $container->addField('active', ['required' => true]);
        $container->addField('role', ['required' => true]);
        $container->addField('note', ['required' => true]);
        return $container;
    }
}
