<?php

namespace CommonModule;

use FKSDB\Components\Grids\ContestantsFromSchoolGrid;
use FKSDB\Components\Grids\SchoolsGrid;
use FKSDB\EntityTrait;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceSchool;
use Nette\Application\BadRequestException;

/**
 * Class SchoolPresenter
 * @package CommonModule
 * @method ModelSchool getEntity()
 */
class SchoolPresenter extends BasePresenter {

    use EntityTrait;

    /**
     * @return string
     */
    protected function getModelResource(): string {
        return ModelSchool::RESOURCE_ID;
    }

    /**
     * @var ServiceSchool
     */
    private $serviceSchool;

    /**
     * @param ServiceSchool $serviceSchool
     */
    public function injectServiceSchool(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    public function titleList() {
        $this->setTitle(_('Schools'));
        $this->setIcon('fa fa-university');
    }

    /**
     * @throws BadRequestException
     */
    public function titleDetail() {
        $school = $this->getEntity();
        $this->setTitle(sprintf(_('Detail of school %s'), $school->name_abbrev));
        $this->setIcon('fa fa-university');
    }

    /**
     * @throws BadRequestException
     */
    public function renderDetail() {
        $this->template->model = $this->getEntity();
    }

    /**
     * @return IService
     */
    protected function getORMService() {
        return $this->serviceSchool;
    }


    /**
     * @return SchoolsGrid
     */
    protected function createComponentGrid(): SchoolsGrid {
        return new SchoolsGrid($this->serviceSchool);
    }


    /**
     * @return ContestantsFromSchoolGrid
     * @throws BadRequestException
     */
    protected function createComponentContestantsFromSchoolGrid(): ContestantsFromSchoolGrid {
        return new ContestantsFromSchoolGrid($this->getEntity(), $this->getORMService());
    }
}
