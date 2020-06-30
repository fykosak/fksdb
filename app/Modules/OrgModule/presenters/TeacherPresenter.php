<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Entity\Teacher\TeacherForm;
use FKSDB\Components\Grids\TeachersGrid;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\ORM\Models\ModelTeacher;
use FKSDB\ORM\Services\ServiceTeacher;
use FKSDB\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class TeacherPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelTeacher getEntity()
 */
class TeacherPresenter extends BasePresenter {
    use EntityPresenterTrait;

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

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleEdit() {
        $this->setPageTitle(new PageTitle(sprintf(_('Edit teacher %s'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-pencil'));
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(_('Create new teacher'), 'fa fa-plus');
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(_('Teacher'), 'fa fa-graduation-cap');
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail() {
        $this->setPageTitle(new PageTitle(_('Teacher detail'), 'fa fa-graduation-cap'));
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
     * @param IResource|string|null $resource
     * @param string|null $privilege
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
