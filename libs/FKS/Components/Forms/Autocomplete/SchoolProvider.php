<?php

namespace FKS\Components\Forms\Controls\Autocomplete;

use ModelSchool;
use Nette\NotImplementedException;
use ServiceSchool;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class SchoolProvider implements IFilteredDataProvider {

    const LIMIT = 50;

    /**
     * @var ServiceSchool
     */
    private $serviceSchool;

    function __construct(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    /**
     * Prefix search.
     * 
     * @param string $search
     * @return array
     */
    public function getFilteredItems($search) {
        $search = trim($search);
        $tokens = preg_split('/[ ,\.]+/', $search);

        $schools = $this->serviceSchool->getTable();
        foreach ($tokens as $token) {
            $schools->where('name_full LIKE concat(\'%\', ?, \'%\')', $token);
        }
        $schools->order('name_abbrev');

        if (count($schools) > self::LIMIT) {
            return array();
        }

        $result = array();
        foreach ($schools as $school) {
            $result[] = $this->getItem($school);
        }
        return $result;
    }

    public function getItemLabel($id) {
        $school = $this->serviceSchool->findByPrimary($id);
        return $school->name_abbrev;
    }

    public function getItems() {
        throw new NotImplementedException();
    }

    private function getItem(ModelSchool $school) {
        return array(
            self::LABEL => $school->name_abbrev,
            self::VALUE => $school->school_id,
        );
    }

}
