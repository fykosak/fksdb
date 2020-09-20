<?php

namespace FKSDB\Persons\Deduplication;

use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPersonInfo;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use Nette\Utils\Strings;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DuplicateFinder {

    public const IDX_PERSON = 'person';
    public const IDX_SCORE = 'score';
    public const DIFFERENT_PATTERN = 'not-same';

    private ServicePerson $servicePerson;

    /** @var array */
    private $parameters;

    public function __construct(ServicePerson $servicePerson, Container $container) {
        $this->servicePerson = $servicePerson;
        $this->parameters = $container->getParameters()['deduplication']['finder'];
    }

    public function getPairs(): array {
        $buckets = [];
        /* Create buckets for quadratic search. */
        /** @var ModelPerson $person */
        foreach ($this->servicePerson->getTable()->select("person.*, :person_info.email, :person_info.duplicates, :person_info.person_id AS 'PI'") as $person) {
            $bucketKey = $this->getBucketKey($person);
            if (!isset($buckets[$bucketKey])) {
                $buckets[$bucketKey] = [];
            }
            $buckets[$bucketKey][] = $person;
        }

        /* Run quadratic comparison in each bucket */
        $pairs = [];
        foreach ($buckets as $bucket) {
            foreach ($bucket as $personA) {
                foreach ($bucket as $personB) {
                    if ($personA->person_id >= $personB->person_id) {
                        continue;
                    }
                    $score = $this->getSimilarityScore($personA, $personB);
                    if ($score > $this->parameters['threshold']) {
                        $pairs[$personA->person_id] = [
                            self::IDX_PERSON => $personB,
                            self::IDX_SCORE => $score,
                        ];
                        continue; // we search only pairs, so each equivalence class is decomposed into pairs
                    }
                }
            }
        }
        return $pairs;
    }

    private function getBucketKey(ModelPerson $row): string {
        $fam = Strings::webalize($row->family_name);
        return substr($fam, 0, 3) . substr($fam, -1);
        //return $row->gender . mb_substr($row->family_name, 0, 2);
    }

    /**
     * @param ModelPerson|ModelPersonInfo $a
     * @param ModelPerson|ModelPersonInfo $b
     * @return float
     * @todo Implement more than binary score.
     *
     */
    private function getSimilarityScore(ModelPerson $a, ModelPerson $b) {
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
        } elseif (!$a->email || !$b->email) {
            $emailScore = 0.8; // a little bit more
        } else {
            $emailScore = 1 - $this->relativeDistance($a->email, $b->email);
        }

        $familyScore = $this->stringScore($a->family_name, $b->family_name);
        $otherScore = $this->stringScore($a->other_name, $b->other_name);


        return $this->parameters['familyWeight'] * $familyScore + $this->parameters['otherWeight'] * $otherScore + $this->parameters['emailWeight'] * $emailScore;
    }

    /**
     * @param ActiveRow|ModelPersonInfo $person
     * @return array
     */
    private function getDifferentPersons(ActiveRow $person): array {
        if (!isset($person->duplicates)) {
            return [];
        }
        $differentPersonIds = [];
        foreach (explode(',', $person->duplicates) as $row) {
            if (strtok($row, '(') === self::DIFFERENT_PATTERN) {
                $differentPersonIds[] = strtok(')');
            }
        }
        return $differentPersonIds;
    }

    /**
     * @param string $a
     * @param string $b
     * @return float|int
     */
    private function stringScore($a, $b) {
        return 1 - $this->relativeDistance(Strings::webalize($a), Strings::webalize($b));
    }

    /**
     * @param string $a
     * @param string $b
     * @return float|int
     */
    private function relativeDistance($a, $b) {
        $maxLen = max(strlen($a), strlen($b));
        if ($maxLen == 0) {
            return 0; // two empty strings are equal
        }
        return levenshtein($a, $b) / $maxLen;
    }

}
