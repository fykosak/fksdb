<?php

namespace Persons;

use Nette\Object;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonHistory;
use ServicePersonInfo;
use ServicePersonHasFlag;
use ServiceFlag;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedPersonHandlerFactory extends Object {

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;

    /**
     * @var ServicePersonHistory
     */
    private $servicePersonHistory;

    /**
     * @var ServiceMPostContact
     */
    private $serviceMPostContact;
    
    /**
     * @var ServicePersonHasFlag
     */
    private $servicePersonHasFlag;
    
    /**
     * @var ServiceFlag
     */
    private $serviceFlag;

    function __construct(ServicePerson $servicePerson, ServicePersonInfo $servicePersonInfo, ServicePersonHistory $servicePersonHistory, ServiceMPostContact $serviceMPostContact, ServicePersonHasFlag $servicePersonHasFlag, ServiceFlag $serviceFlag) {
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->servicePersonHistory = $servicePersonHistory;
        $this->serviceMPostContact = $serviceMPostContact;
        $this->servicePersonHasFlag = $servicePersonHasFlag;
        $this->serviceFlag = $serviceFlag;
    }

    public function create($acYear, $resolution = ReferencedPersonHandler::RESOLUTION_EXCEPTION) {
        return new ReferencedPersonHandler(
                $this->servicePerson, $this->servicePersonInfo, $this->servicePersonHistory, $this->serviceMPostContact, $this->servicePersonHasFlag, $this->serviceFlag, $acYear, $resolution
        );
    }

}

