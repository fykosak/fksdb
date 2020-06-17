<?php

namespace OrgModule;

use FKSDB\Components\Controls\Entity\Teacher\TeacherForm;
use FKSDB\Components\Grids\TeachersGrid;
use FKSDB\EntityTrait;
use FKSDB\ORM\Models\ModelTeacher;
use FKSDB\ORM\Services\ServiceTeacher;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;

/**
 * Class TeacherPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelTeacher getEntity()
 */
class TeacherPresenter extends BasePresenter {
    use EntityTrait;

    /**
     * @var ServiceTeacher
     */
    private $serviceTeacher;

    /**
     * @param ServiceTeacher $serviceTeacher
     * @return void
     */
    public function injectServiceTeacher(ServiceTeacher $serviceTeacher) {
        $this->serviceTeacher = $serviceTeacher;
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

    public function renderDetail() {
        $this->template->model = $this->getEntity();
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function actionEdit() {
        $this->traitActionEdit();
    }

    protected function createComponentGrid(): TeachersGrid {
        return new TeachersGrid($this->getContext());
    }

    protected function createComponentCreateForm(): Control {
        return new TeacherForm($this->getContext(), true);
    }

    protected function createComponentEditForm(): Control {
        return new TeacherForm($this->getContext(), false);
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

    protected function getORMService(): ServiceTeacher {
        return $this->serviceTeacher;
    }

    protected function getModelResource(): string {
        return ModelTeacher::RESOURCE_ID;
    }

}
