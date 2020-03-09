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
 * @method ModelSchool loadEntity(int $id)
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
     * @param int $id
     * @throws BadRequestException
     */
    public function titleDetail(int $id) {
        $this->setTitle(sprintf(_('Detail of school %s'), $this->loadEntity($id)->name_abbrev));
        $this->setIcon('fa fa-university');
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function actionDetail(int $id) {
        $this->loadEntity($id);
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function renderDetail(int $id) {
        $this->template->model = $this->loadEntity($id);
    }

    /**
     * @return IService|ServiceSchool
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
     */
    protected function createComponentContestantsFromSchoolGrid(): ContestantsFromSchoolGrid {
        return new ContestantsFromSchoolGrid($this->getEntity(), $this->getORMService());
    }
}
