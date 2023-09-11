<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Components\DataTest\Tests\Person\EventCoveringTest;
use FKSDB\Components\DataTest\Tests\Person\GenderFromBornNumberTest;
use FKSDB\Components\DataTest\Tests\Person\ParticipantsDurationTest;
use FKSDB\Components\DataTest\Tests\Person\PersonHistoryAdapter;
use FKSDB\Components\DataTest\Tests\Person\PersonInfoAdapter;
use FKSDB\Components\DataTest\Tests\Person\PostgraduateStudyTest;
use FKSDB\Components\DataTest\Tests\Person\SchoolChangeTest;
use FKSDB\Components\DataTest\Tests\Person\StudyYearTest;
use FKSDB\Components\DataTest\Tests\PersonHistory\StudyTypeTest;
use FKSDB\Components\DataTest\Tests\PersonHistory\SetSchoolTest;
use FKSDB\Components\DataTest\Tests\PersonInfo\PersonInfoFileLevelTest;
use FKSDB\Components\DataTest\Tests\School\StudyYearFillTest;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;

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
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->registersTests();
    }

    private function registersTests(): void
    {
        $this->tests = [
            'person' => [
                'gender_born_number' => new GenderFromBornNumberTest($this->container),
                'participants_duration' => new ParticipantsDurationTest($this->container),
                'organization_participation' => new EventCoveringTest($this->container),
                'study_year' => new StudyYearTest($this->container),
                'school_study' => new PersonHistoryAdapter(new StudyTypeTest($this->container), $this->container),
                'school_set' => new PersonHistoryAdapter(new SetSchoolTest($this->container), $this->container),
                'postgraduate' => new PostgraduateStudyTest($this->container),
                'school_change' => new SchoolChangeTest($this->container),
                'phone' => new PersonInfoAdapter(
                    new PersonInfoFileLevelTest('phone', $this->container),
                    $this->container
                ),
                'phone_parent_d' => new PersonInfoAdapter(
                    new PersonInfoFileLevelTest('phone_parent_d', $this->container),
                    $this->container
                ),
                'phone_parent_m' => new PersonInfoAdapter(
                    new PersonInfoFileLevelTest('phone_parent_m', $this->container),
                    $this->container
                ),
            ],
            'school' => [
                'study' => new StudyYearFillTest($this->container),
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
