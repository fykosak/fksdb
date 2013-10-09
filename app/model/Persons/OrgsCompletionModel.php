<?php

namespace Persons;

use ModelContest;
use ModelOrg;
use OOB\Forms\IItemsModel;
use ServiceOrg;
use YearCalculator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class OrgsCompletionModel implements IItemsModel {

    /**
     * @var ModelContest
     */
    private $contest;

    /**
     * @var ServiceOrg
     */
    private $serviceOrg;

    /**
     *
     * @var YearCalculator
     */
    private $yearCalculator;

    function __construct(ModelContest $contest, ServiceOrg $serviceOrg, YearCalculator $yearCalculator) {
        $this->contest = $contest;
        $this->serviceOrg = $serviceOrg;
        $this->yearCalculator = $yearCalculator;
    }

    public function GetAllItems() {
        $orgs = $this->serviceOrg->getTable()->where(array(
            'contest_id' => $this->contest->contest_id
        ));

        $currentYear = $this->yearCalculator->getCurrentYear($this->contest);
        $orgs->where('since <= ?', $currentYear);
        $orgs->where('until IS NULL OR until <= ?', $currentYear);

        $result = array();
        foreach ($orgs as $org) {
            $name = $this->formatLabel($org);
            $result[$org->org_id] = $name;
        }

        // TODO would be better use collation on the database
        setlocale(LC_COLLATE, 'cs_CZ.utf8');

        uasort($result, function($a, $b) {
                    return strcoll($a, $b);
                });

        return $result;
    }

    public function IdToName($id) {
        $org = $this->serviceOrg->findByPrimary($id);
        return $this->formatLabel($org);
    }

    public function NameToId($name, $insert = false) {
        $matches = array();
        if (preg_match('/.* \(([0-9]+)\)/', $name, $matches)) {
            $orgId = $matches[1];
            if ($this->serviceOrg->findByPrimary($orgId)) {
                return $orgId;
            }
        }

        return null;
    }

    private function formatLabel(ModelOrg $org) {
        return $org->getPerson()->getFullname() . ' (' . $org->org_id . ')';
    }

}
