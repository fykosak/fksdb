<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Entity\TeacherFormComponent;
use FKSDB\Components\Grids\TeachersGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Models\ORM\Models\ModelTeacher;
use FKSDB\Models\ORM\Services\ServiceTeacher;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

/**
 * Class TeacherPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelTeacher getEntity()
 */
class TeacherPresenter extends BasePresenter {
    use EntityPresenterTrait;

    private ServiceTeacher $serviceTeacher;

    final public function injectServiceTeacher(ServiceTeacher $serviceTeacher): void {
        $this->serviceTeacher = $serviceTeacher;
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Edit teacher %s'), $this->getEntity()->getPerson()->getFullName()), 'fas fa-pen'));
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(_('Create new teacher'), 'fas fa-plus');
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(_('Teacher'), 'fas fa-graduation-cap');
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(_('Teacher detail'), 'fas fa-graduation-cap'));
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     */
    public function renderDetail(): void {
        $this->template->model = $this->getEntity();
    }

    protected function createComponentGrid(): TeachersGrid {
        return new TeachersGrid($this->getContext());
    }

    protected function createComponentCreateForm(): TeacherFormComponent {
        return new TeacherFormComponent($this->getContext(), null);
    }

    /**
     * @return TeacherFormComponent
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): TeacherFormComponent {
        return new TeacherFormComponent($this->getContext(), $this->getEntity());
    }

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    protected function getORMService(): ServiceTeacher {
        return $this->serviceTeacher;
    }

    protected function getModelResource(): string {
        return ModelTeacher::RESOURCE_ID;
    }
}
