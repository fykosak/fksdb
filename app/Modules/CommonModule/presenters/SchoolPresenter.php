<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Components\Controls\Entity\School\SchoolFormComponent;
use FKSDB\Components\Grids\SchoolsGrid;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceSchool;
use FKSDB\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class SchoolPresenter
 * *
 * @method ModelSchool getEntity()
 */
class SchoolPresenter extends BasePresenter {
    use EntityPresenterTrait;

    /** @var ServiceSchool */
    private $serviceSchool;

    /**
     * @param ServiceSchool $serviceSchool
     * @return void
     */
    public function injectServiceSchool(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(_('Schools'), 'fa fa-university');
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(_('Založit školu'), 'fa fa-plus');
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleEdit() {
        $this->setPageTitle(new PageTitle(sprintf(_('Úprava školy %s'), $this->getEntity()->name_abbrev), 'fa fa-pencil'));
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail() {
        $this->setPageTitle(new PageTitle(sprintf(_('Detail of school %s'), $this->getEntity()->name_abbrev), 'fa fa-university'));
    }

    /**
     * @throws BadRequestException
     */
    public function actionEdit() {
        $this->traitActionEdit();
    }

    /**
     * @return void
     */
    public function renderDetail() {
        $this->template->model = $this->getEntity();
    }

    protected function createComponentGrid(): SchoolsGrid {
        return new SchoolsGrid($this->getContext());
    }

    protected function createComponentEditForm(): Control {
        return new SchoolFormComponent($this->getContext(), false);
    }

    protected function createComponentCreateForm(): Control {
        return new SchoolFormComponent($this->getContext(), true);
    }

    /**
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isAnyContestAuthorized($resource, $privilege);
    }

    protected function getORMService(): ServiceSchool {
        return $this->serviceSchool;
    }
}
