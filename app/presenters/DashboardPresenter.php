<?php

/**
 * Homepage presenter.
 */
class DashboardPresenter extends AuthenticatedPresenter {

    public function renderDefault() {
        $this->template->anyVariable = 'any value';


//                $service = ServicePerson::getInstance();
//                $person = $service->findByPrimary(1);
//                
//                $srv2 = ServiceMPostContact::getInstance();
        $srv2 = $this->getService('ServiceLogin');
        $login = $srv2->findByPrimary('1');
////                $mpc = $srv2->createNew(array('street' => 'Lobačevská'));
////                $mpc->getJoinedModel()->person_id = $person->person_id;
////                
////                $srv2->save($mpc);
//                
//                $srv = ServicePostContact::getInstance();
//                $arr = array();
//                while($contact = $srv->where(array('person_id' => $person->getPrimary()))->fetch()){
//                    $arr[] = $srv2->composeModel($contact->getAddress(), $contact);
//                };
//                
//                foreach($arr as $pcm){
//                    $srv2->dispose($pcm);
//                }
//                
//                $org = $oservice->createNew();                
//                $org->contest_id = 1;
//                $org->since = 1;
//                $org->order= 1;
//                $org->person_id = $person->getPrimary();
//                $oservice->save($org);
//                $personA = $table->find(1)->fetch();
//                $personB = $table->find(1)->fetch();
//                
//                $dbb = $this->context->getService('DebugBar');
//                echo "..A   ".$personA == $personB . "\n";
//                //$obj->person_id = 1;
        //$obj->update();
    }

}
