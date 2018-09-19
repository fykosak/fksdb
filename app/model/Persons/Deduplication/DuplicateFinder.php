<?php

namespace Persons\Deduplication;

use FKSDB\Config\GlobalParameters;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Strings;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DuplicateFinder {

    const IDX_PERSON = 'person';
    const IDX_SCORE = 'score';
    const DIFFERENT_PATTERN = 'not-same';

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var array
     */
    private $parameters;

    function __construct(ServicePerson $servicePerson, GlobalParameters $parameters) {
        $this->servicePerson = $servicePerson;
        $this->parameters = $parameters['deduplication']['finder'];
    }

    public function getPairs() {
        $buckets = [];
        /* Create buckets for quadratic search. */
        foreach ($this->servicePerson->getTable()->select("person.*, person_info:email, person_info:duplicates, person_info:person_id AS 'PI'") as $person) {
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
                    $score = $this->getSimilarityScore($personA, $personB);
                    if ($score > $this->parameters['threshold']) {
                        $pairs[$personA->person_id] = array(
                            self::IDX_PERSON => $personB,
                            self::IDX_SCORE => $score,
                        );
                        continue; // we search only pairs, so each equivalence class is decomposed into pairs
                    }
                }
            }
        }
        return $pairs;
    }

    private function getBucketKey(ActiveRow $row) {
        $fam = Strings::webalize($row->family_name);
        return substr($fam, 0, 3) . substr($fam, -1);
        //return $row->gender . mb_substr($row->family_name, 0, 2);
    }

    /**
     * @todo Implement more than binary score.
     *
     * @param ActiveRow $a
     * @param ActiveRow $b
     * @return float
     */
    private function getSimilarityScore(ActiveRow $a, ActiveRow $b) {
        /*
         * Check explixit difference
         */
        if (in_array($a->getPrimary(), $this->getDifferentPersons($b))) {
            return 0;
        }
        if (in_array($b->getPrimary(), $this->getDifferentPersons($a))) {
            return 0;
        }

        /*
         * Email check
         */
        if (!$a->PI || !$b->PI) { // if person_info records don't exist
            $emailScore = 0.5; // cannot say anything
        } else if (!$a->email || !$b->email) {
            $emailScore = 0.8; // a little bit more
        } else {
            $emailScore = 1 - $this->relativeDistance($a->email, $b->email);
        }

        $familyScore = $this->stringScore($a->family_name, $b->family_name);
        $otherScore = $this->stringScore($a->other_name, $b->other_name);


        return $this->parameters['familyWeight'] * $familyScore + $this->parameters['otherWeight'] * $otherScore + $this->parameters['emailWeight'] * $emailScore;
    }

    private function getDifferentPersons(ActiveRow $person) {
        if (!isset($person->duplicates)) {
            return array();
        }
        $differentPersonIds = [];
        foreach (explode(',', $person->duplicates) as $row) {
            if (strtok($row, '(') === self::DIFFERENT_PATTERN) {
                $differentPersonIds[] = strtok(')');
            }
        }
        return $differentPersonIds;
    }

    private function stringScore($a, $b) {
        return 1 - $this->relativeDistance(Strings::webalize($a), Strings::webalize($b));
    }

    private function relativeDistance($a, $b) {
        $maxLen = max(strlen($a), strlen($b));
        if ($maxLen == 0) {
            return 0; // two empty strings are equal
        }
        return levenshtein($a, $b) / $maxLen;
    }

}

