<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKS\Components\Forms\Controls\Autocomplete\IDataProvider;
use ModelContest;
use ModelOrg;
use ServiceOrg;
use YearCalculator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class OrgProvider implements IDataProvider {

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

    public function GetItems() {
        $orgs = $this->serviceOrg->getTable()->where(array(
            'contest_id' => $this->contest->contest_id
        ));

        $currentYear = $this->yearCalculator->getCurrentYear($this->contest);
        $orgs->where('since <= ?', $currentYear);
        $orgs->where('until IS NULL OR until <= ?', $currentYear);

        $result = array();
        foreach ($orgs as $org) {
            $result[] = array(
                self::LABEL => $this->formatLabel($org),
                self::VALUE => $org->org_id,
            );
        }

        // TODO would be better use collation on the database
        setlocale(LC_COLLATE, 'cs_CZ.utf8');

        usort($result, function($a, $b) {
                    return strcoll($a[self::LABEL], $b[self::LABEL]);
                });

        return $result;
    }

    private function formatLabel(ModelOrg $org) {
        return $org->getPerson()->getFullname();
    }

    public function getItemLabel($id) {
        $org = $this->serviceOrg->findByPrimary($id);
        return $this->formatLabel($org);
    }

}
