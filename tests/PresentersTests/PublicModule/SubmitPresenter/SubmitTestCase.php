<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

use FKSDB\Tests\MockEnvironment\MockApplicationTrait;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Database\Row;
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
    protected int $taskAll;
    protected int $taskRestricted;
    protected int $personId;
    protected int $contestantId;
    protected IPresenter $fixture;

    /**
     * SubmitTestCase constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Environment::lock(LOCK_UPLOAD, TEMP_DIR);

        $this->taskAll = $this->insert('task', [
            'label' => '1',
            'series' => '1',
            'year' => '1',
            'contest_id' => '1',
        ]);
        $this->insert('task_study_year', [
            'task_id' => $this->taskAll,
            'study_year' => '6',
        ]);
        $this->insert('task_study_year', [
            'task_id' => $this->taskAll,
            'study_year' => '7',
        ]);
        $this->insert('task_study_year', [
            'task_id' => $this->taskAll,
            'study_year' => '8',
        ]);
        $this->insert('task_study_year', [
            'task_id' => $this->taskAll,
            'study_year' => '9',
        ]);

        $this->taskRestricted = $this->insert('task', [
            'label' => '2',
            'series' => '1',
            'year' => '1',
            'contest_id' => '1',
        ]);
        $this->insert('task_study_year', [
            'task_id' => $this->taskRestricted,
            'study_year' => '6',
        ]);
        $this->insert('task_study_year', [
            'task_id' => $this->taskRestricted,
            'study_year' => '7',
        ]);

        $this->personId = $this->createPerson('Matyáš', 'Korvín', [], []);
        $this->contestantId = $this->insert('contestant_base', [
            'contest_id' => 1,
            'year' => 1,
            'person_id' => $this->personId,
        ]);

        $this->fixture = $this->createPresenter('Public:Submit');
        $this->authenticate($this->personId, $this->fixture);
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
            'tasks' => "$this->taskAll,$this->taskRestricted",
            '_token_' => self::TOKEN,
        ]);

        $request->setFiles([
            "task$this->taskAll" => $this->createFileUpload(),
            "task$this->taskRestricted" => $this->createFileUpload(),
        ]);
        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);

        $this->assertSubmit($this->contestantId, $this->taskAll);
    }

    protected function assertSubmit(int $contestantId, int $taskId): Row
    {
        $submit = $this->explorer->fetch(
            'SELECT * FROM submit WHERE ct_id = ? AND task_id = ?',
            $contestantId,
            $taskId
        );
        Assert::notEqual(null, $submit);
        return $submit;
    }

    protected function assertNotSubmit(int $contestantId, int $taskId): void
    {
        $submit = $this->explorer->fetch(
            'SELECT * FROM submit WHERE ct_id = ? AND task_id = ?',
            $contestantId,
            $taskId
        );
        Assert::equal(null, $submit);
    }
}
