<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Components\DataTest\Tests\Person\EventCoveringTest;
use FKSDB\Components\DataTest\Tests\Person\GenderFromBornNumberTest;
use FKSDB\Components\DataTest\Tests\Person\ParticipantsDurationTest;
use FKSDB\Components\DataTest\Tests\Person\PersonHistoryAdapter;
use FKSDB\Components\DataTest\Tests\Person\PersonInfoAdapter;
use FKSDB\Components\DataTest\Tests\Person\SchoolChangeTest;
use FKSDB\Components\DataTest\Tests\Person\StudyYearTest;
use FKSDB\Components\DataTest\Tests\PersonHistory\SchoolStudyTest;
use FKSDB\Components\DataTest\Tests\PersonHistory\SetSchoolTest;
use FKSDB\Components\DataTest\Tests\PersonInfo\PersonInfoFileLevelTest;
use FKSDB\Components\DataTest\Tests\School\StudyYearFillTest;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;

/**
 * @phpstan-type TTests array{
 * person:array<string,Test<\FKSDB\Models\ORM\Models\PersonModel>>,
 * school:array<string,Test<\FKSDB\Models\ORM\Models\SchoolModel>>,
 * }
 */
class DataTestFactory
{
    /** @phpstan-var TTests */
    private array $tests;
    private ORMFactory $tableReflectionFactory;

    public function __construct(ORMFactory $tableReflectionFactory)
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->registersTests();
    }

    private function registersTests(): void
    {
        $this->tests = [
            'person' => [
                'gender_born_number' => new GenderFromBornNumberTest(),
                'participants_duration' => new ParticipantsDurationTest(),
                'organization_participation' => new EventCoveringTest(),
                'study_year' => new StudyYearTest(),
                'school_study' => new PersonHistoryAdapter(new SchoolStudyTest()),
                'school_set' => new PersonHistoryAdapter(new SetSchoolTest()),
                'school_change' => new SchoolChangeTest(),
                'phone' => new PersonInfoAdapter(
                    new PersonInfoFileLevelTest($this->tableReflectionFactory, 'phone')
                ),
                'phone_parent_d' => new PersonInfoAdapter(
                    new PersonInfoFileLevelTest(
                        $this->tableReflectionFactory,
                        'phone_parent_d'
                    )
                ),
                'phone_parent_m' => new PersonInfoAdapter(
                    new PersonInfoFileLevelTest(
                        $this->tableReflectionFactory,
                        'phone_parent_m'
                    )
                ),
            ],
            'school' => [
                'study' => new StudyYearFillTest(),
            ],
        ];
    }

    /**
     * @phpstan-return value-of<TTests>
     * @return Test[]
     */
    public function getTests(string $section): array
    {
        return $this->tests[$section] ?? [];
    }

    /**
     * @phpstan-template TModel of Model
     * @phpstan-param array<string,Test<TModel>> $tests
     * @phpstan-param TModel $model
     * @phpstan-return array<string,Message[]>
     */
    public static function runForModel(Model $model, array $tests): array
    {
        $log = [];
        foreach ($tests as $testId => $test) {
            $logger = new MemoryLogger();
            $test->run($logger, $model);
            $testLog = $logger->getMessages();
            if (count($testLog)) {
                $log[$testId] = $testLog;
            }
        }
        return $log;
    }
}
