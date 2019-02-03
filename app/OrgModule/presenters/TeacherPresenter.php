<?php

namespace OrgModule;

use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Components\Forms\Factories\TeacherFactory;
use FKSDB\Components\Grids\TeachersGrid;
use Nette\Application\UI\Form;
use Persons\ExtendedPersonHandler;
use ServiceTeacher;

class TeacherPresenter extends ExtendedPersonPresenter {

    protected $modelResourceId = 'teacher';
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

    public function injectServiceTeacher(ServiceTeacher $serviceTeacher) {
        $this->serviceTeacher = $serviceTeacher;
    }

    public function injectTeacherFactory(TeacherFactory $teacherFactory) {
        $this->teacherFactory = $teacherFactory;
    }

    public function injectSchoolFactory(SchoolFactory $schoolFactory) {
        $this->schoolFactory = $schoolFactory;
    }

    public function titleEdit() {
        /**
         * @var $model \FKSDB\ORM\ModelTeacher
         */
        $model = $this->getModel();
        $this->setTitle(sprintf(_('Edit teacher %s'), $model->getPerson()->getFullName()));
        $this->setIcon('fa fa-pencil');
    }

    public function titleCreate() {
        $this->setTitle(_('Create new teacher'));
        $this->setIcon('fa fa-plus');
    }

    public function titleList() {
        $this->setTitle(_('Teacher'));
        $this->setIcon('fa fa-graduation-cap');
    }

    /**
     * @param $name
     * @return TeachersGrid
     */
    protected function createComponentGrid($name): TeachersGrid {
        return new TeachersGrid($this->serviceTeacher);
    }

    protected function appendExtendedContainer(Form $form) {
        $container = $this->teacherFactory->createTeacher();
        $schoolContainer = $this->schoolFactory->createSchoolSelect();
        $container->addComponent($schoolContainer, 'school_id');
        $form->addComponent($container, ExtendedPersonHandler::CONT_MODEL);
    }

    protected function getORMService() {
        return $this->serviceTeacher;
    }

    public function messageCreate() {
        return _('Teacher %s has been created.');
    }

    public function messageEdit() {
        return _('Teacher has been edited');
    }

    public function messageError() {
        return _('Error during creating new teacher.');
    }

    public function messageExists() {
        return _('Teacher already exist');
    }
}

