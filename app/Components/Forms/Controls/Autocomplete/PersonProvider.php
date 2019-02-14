<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\ORM\ModelContest;
use FKSDB\ORM\ModelPerson;
use Nette\Database\Table\Selection;
use ServicePerson;
use YearCalculator;

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

    /**
     * @var Selection
     */
    private $searchTable;

    /**
     * PersonProvider constructor.
     * @param ServicePerson $servicePerson
     */
    function __construct(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
        $this->searchTable = $this->servicePerson->getTable();
    }

    /**
     * Syntactic sugar, should be solved more generally.
     * @param ModelContest $contest
     * @param YearCalculator $yearCalculator
     */
    public function filterOrgs(ModelContest $contest, YearCalculator $yearCalculator) {
        $orgs = $this->servicePerson->getTable()->where([
            'org:contest_id' => $contest->contest_id
        ]);

        $currentYear = $yearCalculator->getCurrentYear($contest);
        $orgs->where('org:since <= ?', $currentYear);
        $orgs->where('org:until IS NULL OR org:until <= ?', $currentYear);
        $this->searchTable = $orgs;
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
        $this->searchTable
                ->where('family_name LIKE concat(?, \'%\') OR other_name LIKE concat(?, \'%\') OR concat(other_name, family_name) LIKE concat(?,  \'%\')', $search, $search, $search);
        return $this->getItems();
    }

    /**
     * @param mixed $id
     * @return mixed
     */
    public function getItemLabel($id) {
        $person = $this->servicePerson->findByPrimary($id);
        return $person->getFullname();
    }

    /**
     * @return array
     */
    public function getItems() {
        $persons = $this->searchTable
                ->order('family_name, other_name');


        $result = [];
        foreach ($persons as $person) {
            $result[] = $this->getItem($person);
        }
        return $result;
    }

    /**
     * @param ModelPerson $person
     * @return array
     */
    private function getItem(ModelPerson $person) {
        $place = null;
        $address = $person->getDeliveryAddress();
        if ($address) {
            $place = $address->getAddress()->city;
        }
        return [
            self::LABEL => $person->getFullName(),
            self::VALUE => $person->person_id,
            self::PLACE => $place,
        ];
    }

    /**
     * @param $id
     */
    public function setDefaultValue($id) {
        /* intentionally blank */
    }

}
