<?php

namespace FKSDB\Tests\PresentersTests\PublicModule\SubmitPresenter;

use FKSDB\Modules\PublicModule\SubmitPresenter;
use FKSDB\Tests\ModelTests\DatabaseTestCase;
use MockEnvironment\MockApplicationTrait;
use Nette\Application\Request;
use Nette\Database\Row;
use Nette\DI\Config\Helpers;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\Utils\Finder;
use Tester\Assert;
use Tester\Environment;

abstract class SubmitTestCase extends DatabaseTestCase {

    use MockApplicationTrait;

    const TOKEN = 'foo';
    const FILE_01 = 'file01.pdf';
    /**
     * @var int
     */
    protected $taskAll;
    /**
     * @var int
     */
    protected $taskRestricted;
    /** @var int */
    protected $personId;
    /** @var int */
    protected $contestantId;

    /**
     * @var SubmitPresenter
     */
    protected $fixture;

    /**
     * SubmitTestCase constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
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

        $this->personId = $this->createPerson('Matyáš', 'Korvín', [], true);
        $this->contestantId = $this->insert('contestant_base', [
            'contest_id' => 1,
            'year' => 1,
            'person_id' => $this->personId,
        ]);


        $this->fixture = $this->createPresenter('Public:Submit');
        $this->authenticate($this->personId);
        $this->fakeProtection(self::TOKEN);
    }

    protected function tearDown() {
        $this->connection->query('DELETE FROM submit');
        $this->connection->query('DELETE FROM task');
        $this->connection->query('DELETE FROM contestant_base');
        $params = $this->getContainer()->getParameters();
        $dir = $params['upload']['root'];
        /** @var \SplFileInfo $f */
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

    protected function createPostRequest(array $postData, array $post = []): Request {
        $post = Helpers::merge($post, [
            'action' => 'default',
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'do' => 'uploadForm-form-submit',
        ]);
        return new Request('Public:Submit', 'POST', $post, $postData);
    }

    protected function createFileUpload(): array {
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

    protected function assertSubmit(int $contestantId, int $taskId): Row {
        $submit = $this->connection->fetch('SELECT * FROM submit WHERE ct_id = ? AND task_id = ?', $contestantId, $taskId);
        Assert::notEqual(false, $submit);
        return $submit;
    }

    /**
     * @param int $contestantId
     * @param int $taskId
     * @return void
     */
    protected function assertNotSubmit(int $contestantId, int $taskId) {
        $submit = $this->connection->fetch('SELECT * FROM submit WHERE ct_id = ? AND task_id = ?', $contestantId, $taskId);
        Assert::equal(false, $submit);
    }

}
