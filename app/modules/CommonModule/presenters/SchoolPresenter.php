<?php

namespace FKSDB\CommonModule;

use FKSDB\Components\Controls\Entity\School\SchoolForm;
use FKSDB\Components\Grids\SchoolsGrid;
use FKSDB\EntityTrait;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceSchool;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class SchoolPresenter
 * *
 * @method ModelSchool getEntity()
 */
class SchoolPresenter extends BasePresenter {
    use EntityTrait;

    /** @var ServiceSchool */
    private $serviceSchool;

    /**
     * @param ServiceSchool $serviceSchool
     * @return void
     */
    public function injectServiceSchool(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    public function titleList() {
        $this->setTitle(_('Schools'), 'fa fa-university');
    }

    public function titleCreate() {
        $this->setTitle(_('Založit školu'), 'fa fa-plus');
    }

    /**
     * @return void
     */
    public function titleEdit() {
        $this->setTitle(sprintf(_('Úprava školy %s'), $this->getEntity()->name_abbrev), 'fa fa-pencil');
    }

    /**
     * @return void
     */
    public function titleDetail() {
        $this->setTitle(sprintf(_('Detail of school %s'), $this->getEntity()->name_abbrev), 'fa fa-university');
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
        return new SchoolForm($this->getContext(), false);
    }

    protected function createComponentCreateForm(): Control {
        return new SchoolForm($this->getContext(), true);
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
