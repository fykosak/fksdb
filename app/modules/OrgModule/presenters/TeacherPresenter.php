<?php

namespace OrgModule;

use Exception;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Components\Forms\Factories\TeacherFactory;
use FKSDB\Components\Grids\TeachersGrid;
use FKSDB\EntityTrait;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelTeacher;
use FKSDB\ORM\Services\ServiceTeacher;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Persons\ExtendedPersonHandler;

/**
 * Class TeacherPresenter
 * *
 * @method ModelTeacher getModel2()
 * @method ModelTeacher getModel()
 * @method ModelTeacher getEntity()
 */
class TeacherPresenter extends ExtendedPersonPresenter {
    use EntityTrait;

    /**
     * TeacherPresenter constructor.
     */
    public function __construct() {
        $this->sendEmail = false;
        parent::__construct();
    }

    /** @var string */
    protected $fieldsDefinition = 'adminTeacher';

    /**
     * @var ServiceTeacher
     */
    private $serviceTeacher;

    /**
     * @var TeacherFactory
     */
    private $teacherFactory;
    /**
     * @var SchoolFactory
     */
    private $schoolFactory;

    /**
     * @param ServiceTeacher $serviceTeacher
     * @return void
     */
    public function injectServiceTeacher(ServiceTeacher $serviceTeacher) {
        $this->serviceTeacher = $serviceTeacher;
    }

    /**
     * @param TeacherFactory $teacherFactory
     * @return void
     */
    public function injectTeacherFactory(TeacherFactory $teacherFactory) {
        $this->teacherFactory = $teacherFactory;
    }

    /**
     * @param SchoolFactory $schoolFactory
     * @return void
     */
    public function injectSchoolFactory(SchoolFactory $schoolFactory) {
        $this->schoolFactory = $schoolFactory;
    }

    public function titleEdit() {
        $this->setTitle(sprintf(_('Edit teacher %s'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-pencil');
    }

    public function titleCreate() {
        $this->setTitle(_('Create new teacher'), 'fa fa-plus');
    }

    public function titleList() {
        $this->setTitle(_('Teacher'), 'fa fa-graduation-cap');
    }

    public function titleDetail() {
        $this->setTitle(_('Teacher detail'), 'fa fa-graduation-cap');
    }

    protected function createComponentGrid(): TeachersGrid {
        return new TeachersGrid($this->getContext());
    }

    /**
     * @param Form $form
     * @return void
     * @throws Exception
     */
    protected function appendExtendedContainer(Form $form) {
        $container = $this->teacherFactory->createTeacher();
        $schoolContainer = $this->schoolFactory->createSchoolSelect();
        $container->addComponent($schoolContainer, 'school_id');
        $form->addComponent($container, ExtendedPersonHandler::CONT_MODEL);
    }

    public function renderDetail() {
        $this->template->model = $this->getEntity();
    }

    protected function getORMService(): ServiceTeacher {
        return $this->serviceTeacher;
    }

    public function messageCreate(): string {
        return _('Teacher %s has been created.');
    }

    public function messageEdit(): string {
        return _('Teacher has been edited');
    }

    public function messageError(): string {
        return _('Error during creating new teacher.');
    }

    public function messageExists(): string {
        return _('Teacher already exist');
    }

    protected function getModelResource(): string {
        return ModelTeacher::RESOURCE_ID;
    }

    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    public function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
