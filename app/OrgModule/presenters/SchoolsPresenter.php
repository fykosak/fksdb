<?php

namespace OrgModule;

use FKSDB\Components\Grids\SchoolsGrid;
use FormSchool;
use FormUtils;
use ModelException;
use Nette\Diagnostics\Debugger;

class SchoolsPresenter extends BasePresenter {

    /**
     * @var str
     * @persistent
     */
    public $backlink = '';

    protected function createComponentFormSchool($name) {
        $form = new FormSchool($this->getService('ServiceCountry'), $this, $name);
        
        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = array($this, 'formSchoolSubmitted');
        

        return $form;
    }
    
    protected function createComponentGridSchools($name){
        $grid = new SchoolsGrid();
        
        return $grid;
    }

    public function formSchoolSubmitted(FormSchool $form) {
        $serviceSchool = $this->getService('ServiceSchool');
        $connection = $serviceSchool->getConnection();
        $values = $form->getValues();
        
        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            // create address
            $serviceAddress = $this->getService('ServiceAddress');
            $dataAddress = $values[FormSchool::ADDRESS];
            $address = $serviceAddress->createNew(FormUtils::emptyStrToNull((array) $dataAddress));
            // TODO detect region from coutry and PSČ and refactor together with ContestantPresenter::processWizard
            $serviceAddress->save($address);
            
            // save school
            $dataSchool = $values[FormSchool::SCHOOL];
            $school = $serviceSchool->createNew(FormUtils::emptyStrToNull((array) $dataSchool));
            $school->address_id = $address->address_id;
            
            $serviceSchool->save($school);
            
            
            if (!$connection->commit()) {
                throw new ModelException();
            }

            $this->flashMessage('Škola přidána.');
            $this->restoreRequest($this->backlink);
            $this->redirect('Schools:default');
        } catch (ModelException $e) {
            $connection->rollBack();
            Debugger::log($e, Debugger::ERROR);
            $this->flashMessage('Škola nebyla přidána, došlo k chybě.', 'error');
            $this->restoreRequest($this->backlink);
            $this->redirect('Schools:default');
        }
    }

}
