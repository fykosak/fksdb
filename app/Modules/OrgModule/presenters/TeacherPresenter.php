<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Entity\Teacher\TeacherFormComponent;
use FKSDB\Components\Grids\TeachersGrid;
use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\ORM\Models\ModelTeacher;
use FKSDB\ORM\Services\ServiceTeacher;
use FKSDB\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\IResource;

/**
 * Class TeacherPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelTeacher getEntity()
 */
class TeacherPresenter extends BasePresenter {
    use EntityPresenterTrait;

    private ServiceTeacher $serviceTeacher;

    public function injectServiceTeacher(ServiceTeacher $serviceTeacher): void {
        $this->serviceTeacher = $serviceTeacher;
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleEdit(): void {
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
     * @throws ForbiddenRequestException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(_('Teacher detail'), 'fa fa-graduation-cap'));
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     */
    public function renderDetail(): void {
        $this->template->model = $this->getEntity();
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     * @throws BadTypeException
     */
    public function actionEdit(): void {
        $this->traitActionEdit();
    }

    protected function createComponentGrid(): TeachersGrid {
        return new TeachersGrid($this->getContext());
    }

    protected function createComponentCreateForm(): TeacherFormComponent {
        return new TeacherFormComponent($this->getContext(), true);
    }

    protected function createComponentEditForm(): TeacherFormComponent {
        return new TeacherFormComponent($this->getContext(), false);
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     * @throws ForbiddenRequestException
     * @throws BadTypeException
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
