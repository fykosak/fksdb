<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\EntityForms\TeacherFormComponent;
use FKSDB\Components\Grids\TeachersGrid;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\TeacherModel;
use FKSDB\Models\ORM\Services\TeacherService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Security\Resource;

final class TeacherPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<TeacherModel> */
    use EntityPresenterTrait;

    private TeacherService $teacherService;

    final public function injectServiceTeacher(TeacherService $teacherService): void
    {
        $this->teacherService = $teacherService;
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
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
     * @throws GoneException
     * @throws NotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function createComponentGrid(): TeachersGrid
    {
        return new TeachersGrid($this->getContext());
    }

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    protected function createComponentCreateForm(): TeacherFormComponent
    {
        return new TeacherFormComponent($this->getContext(), $this->getSelectedContestYear(), null);
    }

    /**
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws NotFoundException
     */
    protected function createComponentEditForm(): TeacherFormComponent
    {
        return new TeacherFormComponent($this->getContext(), $this->getSelectedContestYear(), $this->getEntity());
    }

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    protected function getORMService(): TeacherService
    {
        return $this->teacherService;
    }
}
