<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\EntityForms\TeacherFormComponent;
use FKSDB\Components\Grids\TeachersGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\TeacherModel;
use FKSDB\Models\ORM\Services\TeacherService;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Security\Resource;

/**
 * @method TeacherModel getEntity()
 */
class TeacherPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private TeacherService $teacherService;

    final public function injectServiceTeacher(TeacherService $teacherService): void
    {
        $this->teacherService = $teacherService;
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Edit teacher %s'), $this->getEntity()->person->getFullName()),
            'fas fa-user-edit'
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create new teacher'), 'fas fa-user-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Teacher'), 'fas fa-chalkboard-teacher');
    }

    public function titleDetail(): PageTitle
    {
        return new PageTitle(null, _('Teacher detail'), 'fas fa-chalkboard-teacher');
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    final public function renderDetail(): void
    {
        $this->getTemplate()->model = $this->getEntity();
    }

    protected function createComponentGrid(): TeachersGrid
    {
        return new TeachersGrid($this->getContext());
    }

    protected function createComponentCreateForm(): TeacherFormComponent
    {
        return new TeacherFormComponent($this->getContext(), $this->getSelectedContestYear(), null);
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    protected function createComponentEditForm(): TeacherFormComponent
    {
        return new TeacherFormComponent($this->getContext(), $this->getSelectedContestYear(), $this->getEntity());
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    protected function getORMService(): TeacherService
    {
        return $this->teacherService;
    }

    protected function getModelResource(): string
    {
        return TeacherModel::RESOURCE_ID;
    }
}
