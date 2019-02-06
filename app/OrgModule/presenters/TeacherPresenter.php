<?php

namespace OrgModule;

use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Components\Forms\Factories\TeacherFactory;
use FKSDB\Components\Grids\TeachersGrid;
use Nette\Application\UI\Form;
use Persons\ExtendedPersonHandler;
use ServiceTeacher;

/**
 * Class TeacherPresenter
 * @package OrgModule
 */
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

    public function titleEdit() {
        /**
         * @var \FKSDB\ORM\ModelTeacher $model
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

    /**
     * @param Form $form
     * @return mixed|void
     */
    protected function appendExtendedContainer(Form $form) {
        $container = $this->teacherFactory->createTeacher();
        $schoolContainer = $this->schoolFactory->createSchoolSelect();
        $container->addComponent($schoolContainer, 'school_id');
        $form->addComponent($container, ExtendedPersonHandler::CONT_MODEL);
    }

    /**
     * @return mixed|ServiceTeacher
     */
    protected function getORMService() {
        return $this->serviceTeacher;
    }

    /**
     * @return string
     */
    public function messageCreate() {
        return _('Teacher %s has been created.');
    }

    /**
     * @return string
     */
    public function messageEdit() {
        return _('Teacher has been edited');
    }

    /**
     * @return string
     */
    public function messageError() {
        return _('Error during creating new teacher.');
    }

    /**
     * @return string
     */
    public function messageExists() {
        return _('Teacher already exist');
    }
}

