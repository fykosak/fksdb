<?php

namespace OrgModule;

use Exception;
use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Components\Forms\Factories\TeacherFactory;
use FKSDB\Components\Grids\TeachersGrid;
use FKSDB\ORM\Models\ModelTeacher;
use FKSDB\ORM\Services\ServiceTeacher;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Persons\ExtendedPersonHandler;

/**
 * Class TeacherPresenter
 * @package OrgModule
 * @method ModelTeacher getModel2()
 * @method ModelTeacher getModel()
 */
class TeacherPresenter extends ExtendedPersonPresenter {
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
     */
    public function injectServiceTeacher(ServiceTeacher $serviceTeacher) {
        $this->serviceTeacher = $serviceTeacher;
    }

    /**
     * @param TeacherFactory $teacherFactory
     */
    public function injectTeacherFactory(TeacherFactory $teacherFactory) {
        $this->teacherFactory = $teacherFactory;
    }

    /**
     * @param SchoolFactory $schoolFactory
     */
    public function injectSchoolFactory(SchoolFactory $schoolFactory) {
        $this->schoolFactory = $schoolFactory;
    }

    /**
     * @throws BadRequestException
     */
    public function titleEdit() {
        $model = $this->getModel2();
        $this->setTitle(sprintf(_('Edit teacher %s'), $model->getPerson()->getFullName()), 'fa fa-pencil');
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

    /**
     * @return TeachersGrid
     */
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

    /**
     * @throws BadRequestException
     */
    public function renderDetail() {
        $this->template->model = $this->getModel2();
    }

    /**
     * @return ServiceTeacher
     */
    protected function getORMService() {
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

    /**
     * @inheritDoc
     */
    protected function getModelResource(): string {
        return ModelTeacher::RESOURCE_ID;
    }
}
