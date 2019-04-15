<?php

namespace CommonModule;

use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceSchool;

/**
 * Class SchoolPresenter
 * @package CommonModule
 */
class SchoolPresenter extends BasePresenter {
    /**
     * @var ModelSchool
     */
    private $model;

    protected $modelResourceId = 'school';

    /**
     * @var \FKSDB\ORM\Services\ServiceSchool
     */
    private $serviceSchool;

    /**
     * @param \FKSDB\ORM\Services\ServiceSchool $serviceSchool
     */
    public function injectServiceSchool(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    public function titleList() {
        $this->setTitle(_('Školy'));
        $this->setIcon('fa fa-university');
    }

    public function titleDetail() {
        $school = $this->getModel();
        $this->setTitle(sprintf(_('Detail školy %s'), $school->name_abbrev));
        $this->setIcon('fa fa-university');
    }

    public function renderDetail() {
        $this->template->school = $this->getModel();
    }

    /**
     * @param $id
     * @return ModelSchool
     */
    protected function loadModel($id): ModelSchool {
        return $this->serviceSchool->findByPrimary($id);
    }

    /**
     * @return ModelSchool
     */
    public final function getModel(): ModelSchool {
        if (!$this->model) {
            $this->model = $this->getParameter('id') ? $this->loadModel($this->getParameter('id')) : null;
        }
        return $this->model;
    }
}
