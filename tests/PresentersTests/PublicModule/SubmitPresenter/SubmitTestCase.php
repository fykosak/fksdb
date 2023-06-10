<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\ContestantService;
use FKSDB\Models\ORM\Services\SubmitService;
use FKSDB\Models\ORM\Services\TaskCategoryService;
use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Http\FileUpload;
use Nette\Schema\Helpers;
use Nette\Utils\Finder;
use SplFileInfo;
use Tester\Assert;
use Tester\Environment;

abstract class SubmitTestCase extends DatabaseTestCase
{
    public const TOKEN = 'foo';
    public const FILE_01 = 'file01.pdf';
    protected TaskModel $taskAll;
    protected TaskModel $taskRestricted;
    protected PersonModel $person;
    protected ContestantModel $contestant;
    protected IPresenter $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        Environment::lock(LOCK_UPLOAD, __DIR__ . '/../temp/tester');
        $serviceTask = $this->container->getByType(TaskService::class);
        $taskCategoryService = $this->container->getByType(TaskCategoryService::class);
        $this->taskAll = $serviceTask->storeModel([
            'label' => '1',
            'series' => '1',
            'year' => '1',
            'contest_id' => '1',
        ]);

        $taskCategoryService->storeModel([
            'task_id' => $this->taskAll->task_id,
            'contest_category_id' => 6,
        ]);
        $taskCategoryService->storeModel([
            'task_id' => $this->taskAll->task_id,
            'contest_category_id' => 7,
        ]);
        $taskCategoryService->storeModel([
            'task_id' => $this->taskAll->task_id,
            'contest_category_id' => 8,
        ]);
        $taskCategoryService->storeModel([
            'task_id' => $this->taskAll->task_id,
            'contest_category_id' => 9,
        ]);

        $this->taskRestricted = $serviceTask->storeModel([
            'label' => '2',
            'series' => '1',
            'year' => '1',
            'contest_id' => 1,
        ]);
        $taskCategoryService->storeModel([
            'task_id' => $this->taskRestricted->task_id,
            'contest_category_id' => 6,
        ]);
        $taskCategoryService->storeModel([
            'task_id' => $this->taskRestricted->task_id,
            'contest_category_id' => 7,
        ]);

        $this->person = $this->createPerson('Matyáš', 'Korvín', null, []);
        $this->contestant = $this->container->getByType(ContestantService::class)->storeModel([
            'contest_id' => 1,
            'year' => 1,
            'person_id' => $this->person->person_id,
            'contest_category_id' => $this->getCategory(),
        ]);

        $this->fixture = $this->createPresenter('Public:Submit');
        $this->authenticatePerson($this->person, $this->fixture);
        $this->fakeProtection(self::TOKEN);
    }

    abstract protected function getCategory(): int;

    protected function tearDown(): void
    {
        $params = $this->container->getParameters();
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
        $file = tempnam(__DIR__ . '/../temp/tester', 'upload');
        copy(__DIR__ . DIRECTORY_SEPARATOR . self::FILE_01, $file);

        return [
            'file' => new FileUpload([
                'name' => 'reseni2-8.pdf',
                'type' => 'application/pdf',
                'size' => filesize($file),
                'tmp_name' => $file,
                'error' => 0,
            ]),
        ];
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

    protected function assertSubmit(ContestantModel $contestant, TaskModel $task): SubmitModel
    {
        $submit = $this->container
            ->getByType(SubmitService::class)
            ->getTable()
            ->where(['contestant_id' => $contestant->contestant_id, 'task_id' => $task->task_id])
            ->fetch();
        Assert::notEqual(null, $submit);
        return $submit;
    }

    protected function assertNotSubmit(ContestantModel $contestant, TaskModel $task): void
    {
        $submit = $this->container
            ->getByType(SubmitService::class)
            ->getTable()
            ->where(['contestant_id' => $contestant->contestant_id, 'task_id' => $task->task_id])
            ->fetch();
        Assert::equal(null, $submit);
    }
}
