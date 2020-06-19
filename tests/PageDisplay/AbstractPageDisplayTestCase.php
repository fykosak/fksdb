<?php

namespace FKSDB\Tests\PageDisplay;

use FKSDB\ORM\DbNames;
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
abstract class AbstractPageDisplayTestCase extends \DatabaseTestCase {
    use MockApplicationTrait;

    /** @var int */
    const PERSON_ID = 1;


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

        $this->insert(DbNames::TAB_PERSON, [
            'person_id' => 1,
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);

        $loginId = $this->insert(DbNames::TAB_LOGIN, ['person_id' => self::PERSON_ID, 'active' => 1]);


        $this->insert(DbNames::TAB_GRANT, ['login_id' => $loginId, 'role_id' => 1000, 'contest_id' => 1]);
        $this->authenticate($loginId);
    }

    final protected function createRequest(string $presenterName, array $params): Request {
        $params['lang'] = $params['lang'] ?? 'en';
        $params['action'] = $params['action'] ?? 'default';
        return new Request($presenterName, 'GET', $params);
    }

    /**
     * @dataProvider getPages
     */
    final public function testDisplay(string $presenterName, array $params) {
        $fixture = $this->createPresenter($presenterName);
        $request = $this->createRequest($presenterName, $params);
        $response = $fixture->run($request);
        Assert::type(TextResponse::class, $response);
        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);

        Assert::noError(function () use ($source) {
            return (string)$source;
        });

    }

    abstract public function getPages(): array ;

    protected function tearDown() {
        $this->connection->query('DELETE FROM global_session');
        $this->connection->query('DELETE FROM `grant`');
        $this->connection->query('DELETE FROM login');
        $this->connection->query('DELETE FROM person');
        parent::tearDown();
    }
}

