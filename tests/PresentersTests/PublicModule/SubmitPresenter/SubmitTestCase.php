<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelSubmit;
use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\ORM\Services\ServiceContestant;
use FKSDB\Models\ORM\Services\ServiceSubmit;
use FKSDB\Models\ORM\Services\ServiceTask;
use FKSDB\Models\ORM\Services\ServiceTaskStudyYear;
use FKSDB\Tests\MockEnvironment\MockApplicationTrait;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Schema\Helpers;
use Nette\Utils\Finder;
use SplFileInfo;
use Tester\Assert;
use Tester\Environment;

abstract class SubmitTestCase extends DatabaseTestCase
{
    use MockApplicationTrait;

    public const TOKEN = 'foo';
    public const FILE_01 = 'file01.pdf';
    protected ModelTask $taskAll;
    protected ModelTask $taskRestricted;
    protected ModelPerson $person;
    protected ModelContestant $contestant;
    protected IPresenter $fixture;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Environment::lock(LOCK_UPLOAD, TEMP_DIR);
        $serviceTask = $this->getContainer()->getByType(ServiceTask::class);
        $serviceTaskStudyYear = $this->getContainer()->getByType(ServiceTaskStudyYear::class);
        $this->taskAll = $serviceTask->createNewModel([
            'label' => '1',
            'series' => '1',
            'year' => '1',
            'contest_id' => '1',
        ]);

        $serviceTaskStudyYear->createNewModel([
            'task_id' => $this->taskAll->task_id,
            'study_year' => '6',
        ]);
        $serviceTaskStudyYear->createNewModel([
            'task_id' => $this->taskAll->task_id,
            'study_year' => '7',
        ]);
        $serviceTaskStudyYear->createNewModel([
            'task_id' => $this->taskAll->task_id,
            'study_year' => '8',
        ]);
        $serviceTaskStudyYear->createNewModel([
            'task_id' => $this->taskAll->task_id,
            'study_year' => '9',
        ]);

        $this->taskRestricted = $serviceTask->createNewModel([
            'label' => '2',
            'series' => '1',
            'year' => '1',
            'contest_id' => '1',
        ]);
        $serviceTaskStudyYear->createNewModel([
            'task_id' => $this->taskRestricted->task_id,
            'study_year' => '6',
        ]);
        $serviceTaskStudyYear->createNewModel([
            'task_id' => $this->taskRestricted->task_id,
            'study_year' => '7',
        ]);

        $this->person = $this->createPerson('Matyáš', 'Korvín', [], []);
        $this->contestant = $this->getContainer()->getByType(ServiceContestant::class)->createNewModel([
            'contest_id' => 1,
            'year' => 1,
            'person_id' => $this->person->person_id,
        ]);

        $this->fixture = $this->createPresenter('Public:Submit');
        $this->authenticate($this->person->person_id, $this->fixture);
        $this->fakeProtection(self::TOKEN);
    }

    protected function tearDown(): void
    {
        $this->truncateTables(['submit', 'task', 'contestant_base']);
        $params = $this->getContainer()->getParameters();
        $dir = $params['upload']['root'];
        /** @var SplFileInfo $f */
        foreach (Finder::find('*')->from($dir)->childFirst() as $f) {
            if ($f->isDir()) {
                @rmdir($f->getPathname());
            } else {
                @unlink($f->getPathname());
            }
        }
        rmdir($dir);
        parent::tearDown();
    }

    protected function createPostRequest(array $formData): Request
    {
        $formData = Helpers::merge($formData, [
            '_do' => 'uploadForm-form-submit',
        ]);
        return new Request('Public:Submit', 'POST', [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
        ], $formData);
    }

    protected function createFileUpload(): array
    {
        $file = tempnam(TEMP_DIR, 'upload');
        copy(__DIR__ . DIRECTORY_SEPARATOR . self::FILE_01, $file);

        return ['file' => new FileUpload([
            'name' => 'reseni2-8.pdf',
            'type' => 'application/pdf',
            'size' => filesize($file),
            'tmp_name' => $file,
            'error' => 0,
        ])];
    }

    protected function innerTestSubmit(): void
    {
        $request = $this->createPostRequest([
            'upload' => 'Odeslat',
            'tasks' => $this->taskAll->task_id . ',' . $this->taskRestricted->task_id,
            '_token_' => self::TOKEN,
        ]);

        $request->setFiles([
            'task' . $this->taskAll->task_id => $this->createFileUpload(),
            'task' . $this->taskRestricted->task_id => $this->createFileUpload(),
        ]);
        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);

        $this->assertSubmit($this->contestant, $this->taskAll);
    }

    protected function assertSubmit(ModelContestant $contestant, ModelTask $task): ModelSubmit
    {
        $submit = $this->getContainer()
            ->getByType(ServiceSubmit::class)
            ->getTable()
            ->where(['ct_id' => $contestant->ct_id, 'task_id' => $task->task_id])
            ->fetch();
        Assert::notEqual(null, $submit);
        return $submit;
    }

    protected function assertNotSubmit(ModelContestant $contestant, ModelTask $task): void
    {
        $submit = $this->getContainer()
            ->getByType(ServiceSubmit::class)
            ->getTable()
            ->where(['ct_id' => $contestant->ct_id, 'task_id' => $task->task_id])
            ->fetch();
        Assert::equal(null, $submit);
    }
}
