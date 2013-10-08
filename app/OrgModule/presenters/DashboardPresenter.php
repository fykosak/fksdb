<?php

namespace OrgModule;

use DbNames;
use ServiceSubmit;

/**
 * Homepage presenter.
 */
class DashboardPresenter extends BasePresenter {

    /**
     * @var ServiceSubmit
     */
    private $serviceSubmit;

    public function injectServiceSubmit(ServiceSubmit $serviceSubmit) {
        $this->serviceSubmit = $serviceSubmit;
    }

    public function renderDefault() {
//        $connection = $this->serviceSubmit->getConnection();
//        $connection->beginTransaction();
//
//        /** @var Nette\Database\Table\Selection $table */
//        $table = $this->serviceSubmit->getTable();
//        $submit = $table->find(20331)->fetch();      
//        
//
//        $taskPre = $submit->ref(DbNames::TAB_TASK, 'task_id');
//        //$submit->delete();        
//        //$submit->task_id = 1971;
//        
//        $taskPost = $submit->ref(DbNames::TAB_TASK, 'task_id');
//
//        echo "Pre:\n";
//        dump($taskPre);
//
//        echo "Post:\n";
//        dump($taskPost);
//
//        $connection->rollBack();
    }

}
