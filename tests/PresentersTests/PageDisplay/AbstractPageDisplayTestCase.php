<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Tests\MockEnvironment\MockApplicationTrait;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Nette\DI\Container;
use Tester\Assert;

/**
 * Class PageDisplayTest
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractPageDisplayTestCase extends DatabaseTestCase
{
    use MockApplicationTrait;

    protected int $personId;
    private int $loginId;

    /**
     * PageDisplayTest constructor.
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

        $this->personId = $this->insert(DbNames::TAB_PERSON, [
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);

        $this->loginId = $this->insert(DbNames::TAB_LOGIN, ['person_id' => $this->personId, 'active' => 1]);

        $this->insert(DbNames::TAB_GRANT, ['login_id' => $this->loginId, 'role_id' => 1000, 'contest_id' => 1]);
        $this->authenticate($this->loginId);
    }

    final protected function createRequest(string $presenterName, string $action, array $params): Request
    {
        $params['lang'] = $params['lang'] ?? 'en';
        $params['action'] = $action;
        return new Request($presenterName, 'GET', $params);
    }

    /**
     * @dataProvider getPages
     */
    final public function testDisplay(string $presenterName, string $action, array $params = []): void
    {
        [$presenterName, $action, $params] = $this->transformParams($presenterName, $action, $params);
        $fixture = $this->createPresenter($presenterName);
        $this->authenticate($this->loginId, $fixture);
        $request = $this->createRequest($presenterName, $action, $params);
        $response = $fixture->run($request);
        /** @var TextResponse $response */
        Assert::type(TextResponse::class, $response);
        $source = $response->getSource();
        Assert::type(Template::class, $source);

        Assert::noError(function () use ($source): string {
            return (string)$source;
        });
    }

    protected function transformParams(string $presenterName, string $action, array $params): array
    {
        return [$presenterName, $action, $params];
    }

    abstract public function getPages(): array;

    protected function tearDown(): void
    {
        $this->truncateTables([DbNames::TAB_GRANT, DbNames::TAB_LOGIN, DbNames::TAB_PERSON]);
        parent::tearDown();
    }
}
