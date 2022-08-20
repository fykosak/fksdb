<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Deduplication;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;
use Nette\Utils\Strings;

class DuplicateFinder
{
    public const IDX_PERSON = 'person';
    public const IDX_SCORE = 'score';
    public const DIFFERENT_PATTERN = 'not-same';

    private PersonService $personService;

    private array $parameters;

    public function __construct(PersonService $personService, Container $container)
    {
        $this->personService = $personService;
        $this->parameters = $container->getParameters()['deduplication']['finder'];
    }

    public function getPairs(): array
    {
        $buckets = [];
        /* Create buckets for quadratic search. */
        /** @var PersonModel $person */
        foreach (
            $this->personService->getTable()->select(
                "person.*, :person_info.email, :person_info.duplicates, :person_info.person_id AS 'PI'"
            ) as $person
        ) {
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
                    /** @var PersonModel $personA
                     * @var PersonModel $personB
                     */
                    if ($personA->person_id >= $personB->person_id) {
                        continue;
                    }
                    $score = $this->getSimilarityScore($personA, $personB);
                    if ($score > $this->parameters['threshold']) {
                        $pairs[$personA->person_id] = [
                            self::IDX_PERSON => $personB,
                            self::IDX_SCORE => $score,
                        ];
                        // we search only pairs, so each equivalence class is decomposed into pairs
                    }
                }
            }
        }
        return $pairs;
    }

    private function getBucketKey(PersonModel $row): string
    {
        $fam = Strings::webalize($row->family_name);
        return substr($fam, 0, 3) . substr($fam, -1);
        //return $row->gender . mb_substr($row->family_name, 0, 2);
    }

    /**
     * @param PersonModel|PersonInfoModel $a
     * @param PersonModel|PersonInfoModel $b
     * @todo Implement more than binary score.
     */
    private function getSimilarityScore(PersonModel $a, PersonModel $b): float
    {
        /*
         * Check explicit difference
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

        return $this->parameters['familyWeight'] * $familyScore + $this->parameters['otherWeight'] * $otherScore +
            $this->parameters['emailWeight'] * $emailScore;
    }

    /**
     * @param PersonInfoModel $person
     */
    private function getDifferentPersons(Model $person): array
    {
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

    private function stringScore(string $a, string $b): float
    {
        return 1.0 - $this->relativeDistance(Strings::webalize($a), Strings::webalize($b));
    }

    private function relativeDistance(string $a, string $b): float
    {
        $maxLen = max(strlen($a), strlen($b));
        if ($maxLen == 0) {
            return 0.0; // two empty strings are equal
        }
        return levenshtein($a, $b) / $maxLen;
    }
}
