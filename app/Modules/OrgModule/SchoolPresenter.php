<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\EntityForms\SchoolFormComponent;
use FKSDB\Components\Grids\ContestantsFromSchoolGrid;
use FKSDB\Components\Grids\SchoolsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\ORM\Models\ModelSchool;
use FKSDB\Models\ORM\Services\ServiceSchool;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Security\Resource;

/**
 * @method ModelSchool getEntity()
 */
class SchoolPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private ServiceSchool $serviceSchool;

    final public function injectServiceSchool(ServiceSchool $serviceSchool): void
    {
        $this->serviceSchool = $serviceSchool;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('Schools'), 'fa fa-school');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('Create school'), 'fa fa-plus');
    }

    /**
     * @throws ModelNotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(sprintf(_('Edit school %s'), $this->getEntity()->name_abbrev), 'fas fa-pen');
    }

    /**
     * @throws ModelNotFoundException
     */
    public function titleDetail(): PageTitle
    {
        return new PageTitle(sprintf(_('Detail of school %s'), $this->getEntity()->name_abbrev), 'fa fa-university');
    }

    /**
     * @throws ModelNotFoundException
     */
    final public function renderDetail(): void
    {
        $this->template->model = $this->getEntity();
    }

    protected function createComponentGrid(): SchoolsGrid
    {
        return new SchoolsGrid($this->getContext());
    }

    /**
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): SchoolFormComponent
    {
        return new SchoolFormComponent($this->getContext(), $this->getEntity());
    }

    protected function createComponentCreateForm(): SchoolFormComponent
    {
        return new SchoolFormComponent($this->getContext(), null);
    }

    /**
     * @throws ModelNotFoundException
     */
    protected function createComponentContestantsFromSchoolGrid(): ContestantsFromSchoolGrid
    {
        return new ContestantsFromSchoolGrid($this->getEntity(), $this->getContext());
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAnyContestAuthorized($resource, $privilege);
    }

    protected function getORMService(): ServiceSchool
    {
        return $this->serviceSchool;
    }
}
