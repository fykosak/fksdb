<?php

namespace CommonModule;

use FKSDB\EntityTrait;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceSchool;
use Nette\Application\BadRequestException;

/**
 * Class SchoolPresenter
 * @package CommonModule
 * @method ModelSchool getModel
 */
class SchoolPresenter extends BasePresenter {

    use EntityTrait;

    /**
     * @return string
     */
    protected function getModelResource(): string {
        return 'school';
    }

    protected $modelResourceId = 'school';

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
        $this->setTitle(_('Školy'));
        $this->setIcon('fa fa-university');
    }

    /**
     * @throws BadRequestException
     */
    public function titleDetail() {
        $school = $this->getModel();
        $this->setTitle(sprintf(_('Detail školy %s'), $school->name_abbrev));
        $this->setIcon('fa fa-university');
    }

    /**
     * @throws BadRequestException
     */
    public function renderDetail() {
        $this->template->model = $this->getModel();
    }

    /**
     * @return IService
     */
    protected function getORMService() {
        return $this->serviceSchool;
    }
}
