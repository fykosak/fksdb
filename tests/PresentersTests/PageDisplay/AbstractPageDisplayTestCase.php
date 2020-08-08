<?php

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\ORM\DbNames;
use FKSDB\Tests\ModelTests\DatabaseTestCase;
use MockEnvironment\MockApplicationTrait;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplate;
use Nette\DI\Container;
use Tester\Assert;

/**
 * Class PageDisplayTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractPageDisplayTestCase extends DatabaseTestCase {
    use MockApplicationTrait;

    /** @var int */
    protected $personId;

    /**
     * PageDisplayTest constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();

        $this->personId = $this->insert(DbNames::TAB_PERSON, [
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);

        $loginId = $this->insert(DbNames::TAB_LOGIN, ['person_id' => $this->personId, 'active' => 1]);


        $this->insert(DbNames::TAB_GRANT, ['login_id' => $loginId, 'role_id' => 1000, 'contest_id' => 1]);
        $this->authenticate($loginId);
    }

    final protected function createRequest(string $presenterName, string $action, array $params): Request {
        $params['lang'] = $params['lang'] ?? 'en';
        $params['action'] = $action;
        return new Request($presenterName, 'GET', $params);
    }

    /**
     * @dataProvider getPages
     */
    final public function testDisplay(string $presenterName, string $action, array $params = []) {
        list($presenterName, $action, $params) = $this->transformParams($presenterName, $action, $params);
        $fixture = $this->createPresenter($presenterName);
        $request = $this->createRequest($presenterName, $action, $params);
        $response = $fixture->run($request);
        Assert::type(TextResponse::class, $response);
        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);

        Assert::noError(function () use ($source) {
            return (string)$source;
        });
    }

    protected function transformParams(string $presenterName, string $action, array $params): array {
        return [$presenterName, $action, $params];
    }

    abstract public function getPages(): array;

    protected function tearDown() {
        $this->connection->query('DELETE FROM global_session');
        $this->connection->query('DELETE FROM `grant`');
        $this->connection->query('DELETE FROM login');
        $this->connection->query('DELETE FROM person');
        parent::tearDown();
    }
}

