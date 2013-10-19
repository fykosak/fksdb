<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKS\Components\Forms\Controls\Autocomplete\IFilteredDataProvider;
use ModelPerson;
use Nette\NotImplementedException;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonProvider implements IFilteredDataProvider {
    const PLACE = 'place';

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    function __construct(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * Prefix search.
     * 
     * @param string $search
     * @return array
     */
    public function getFilteredItems($search) {
        $search = trim($search);
        $search = str_replace(' ', '', $search);
        $persons = $this->servicePerson->getTable()
                ->where('family_name LIKE concat(?, \'%\') OR other_name LIKE concat(?, \'%\') OR concat(other_name, family_name) LIKE concat(?,  \'%\')', $search, $search, $search)
                ->order('family_name, other_name');


        $result = array();
        foreach ($persons as $person) {
            $result[] = $this->getItem($person);
        }
        return $result;
    }

    public function getItemLabel($id) {
        $person = $this->servicePerson->findByPrimary($id);
        return $person->getFullname();
    }

    public function getItems() {
        throw new NotImplementedException();
    }

    private function getItem(ModelPerson $person) {
        $place = null;
        $address = $person->getDeliveryAddress();
        if($address) {
            $place = $address->getAddress()->city;
        }
        return array(
            self::LABEL => $person->getFullname(),
            self::VALUE => $person->person_id,
            self::PLACE => $place,
        );
    }

//put your code here
}
