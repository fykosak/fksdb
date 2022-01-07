<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServiceGrant;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Tester\Assert;

abstract class AbstractPageDisplayTestCase extends DatabaseTestCase
{
    protected ModelPerson $person;
    private ModelLogin $login;

    protected function setUp(): void
    {
        parent::setUp();

        $this->person = $this->getContainer()->getByType(ServicePerson::class)->createNewModel([
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);

        $this->login = $this->getContainer()->getByType(ServiceLogin::class)->createNewModel(
            ['person_id' => $this->person->person_id, 'active' => 1]
        );
        $this->getContainer()->getByType(ServiceGrant::class)->createNewModel(
            ['login_id' => $this->login->login_id, 'role_id' => 1000, 'contest_id' => 1]
        );
        $this->authenticateLogin($this->login);
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
        $this->authenticateLogin($this->login, $fixture);
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
