<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\ORM\ModelSchool;
use Nette\InvalidStateException;
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

    /**
     * School with school_id equal to defaulValue is suggested even when it's not
     * active.
     *
     * @var int
     */
    private $defaultValue;

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
            $schools->where('name_full LIKE concat(\'%\', ?, \'%\') OR name_abbrev LIKE concat(\'%\', ?, \'%\')', $token, $token);
        }
	// For backwards compatibility consider NULLs active
	if ($this->defaultValue != null) {
	    $schools->where('(active IS NULL OR active = 1) OR school_id = ?', $this->defaultValue);
	} else {
	    $schools->where('active IS NULL OR active = 1');
	}
        $schools->order('name_abbrev');

        if (count($schools) > self::LIMIT) {
            return [];
        }

        $result = [];
        foreach ($schools as $school) {
            $result[] = $this->getItem($school);
        }
        return $result;
    }

    public function getItemLabel($id) {
        $school = $this->serviceSchool->findByPrimary($id);
        if (!$school) {
            throw new InvalidStateException("Cannot find school with ID '$id'.");
        }
        return $school->name_abbrev;
    }

    public function getItems() {
        throw new NotImplementedException();
    }

    private function getItem(ModelSchool $school) {
        return [
            self::LABEL => $school->name_abbrev,
            self::VALUE => $school->school_id,
        ];
    }

    public function setDefaultValue($id) {
        $this->defaultValue = $id;
    }

}
