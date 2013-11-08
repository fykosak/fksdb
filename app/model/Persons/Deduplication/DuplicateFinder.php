<?php

namespace Persons\Deduplication;

use Nette\Database\Table\ActiveRow;
use Nette\Utils\Strings;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class DuplicateFinder {

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    function __construct(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function getPairs() {
        $buckets = array();
        /* Create buckets for quadratic search. */
        foreach ($this->servicePerson->getTable() as $person) {
            $bucketKey = $this->getBucketKey($person);
            if (!isset($buckets[$bucketKey])) {
                $buckets[$bucketKey] = array();
            }
            $buckets[$bucketKey][] = $person;
        }

        /* Run quadratic comparison in each bucket */
        $pairs = array();
        foreach ($buckets as $bucket) {
            foreach ($bucket as $personA) {
                foreach ($bucket as $personB) {
                    if ($personA->person_id >= $personB->person_id) {
                        continue;
                    }
                    if ($this->getSimilarityScore($personA, $personB)) {
                        $pairs[$personA->person_id] = $personB;
                        continue; // we search only pairs, so each equivalence class is decomposed into pairs
                    }
                }
            }
        }
        return $pairs;
    }

    private function getBucketKey(ActiveRow $row) {
        return $row->gender . mb_substr($row->family_name, 0, 2);
    }

    /**
     * @todo Implement more than binary score.
     * 
     * @param ActiveRow $a
     * @param ActiveRow $b
     * @return float
     */
    private function getSimilarityScore(ActiveRow $a, ActiveRow $b) {
        $checkA = $a->family_name . ':' . $a->other_name;
        $checkA = Strings::webalize($checkA);

        $checkB = $b->family_name . ':' . $b->other_name;
        $checkB = Strings::webalize($checkB);

        return $checkA == $checkB;
    }

}
