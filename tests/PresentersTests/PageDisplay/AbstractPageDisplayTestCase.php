<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PageDisplay;

use FKSDB\Models\ORM\Models\Grant\BaseGrantModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\Grant\BaseGrantService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Tester\Assert;

abstract class AbstractPageDisplayTestCase extends DatabaseTestCase
{
    protected PersonModel $person;
    private LoginModel $login;

    protected function setUp(): void
    {
        parent::setUp();

        $this->person = $this->container->getByType(PersonService::class)->storeModel([
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);

        $this->login = $this->container->getByType(LoginService::class)->storeModel(
            ['person_id' => $this->person->person_id, 'active' => 1]
        );
        $this->container->getByType(BaseGrantService::class)->storeModel(
            ['login_id' => $this->login->login_id, 'role' => BaseGrantModel::Cartesian]
        );
        $this->authenticateLogin($this->login);
    }
    /**
     * @phpstan-param array<scalar> $params
     */
    final protected function createRequest(string $presenterName, string $action, array $params): Request
    {
        $params['lang'] = $params['lang'] ?? 'en';
        $params['action'] = $action;
        return new Request($presenterName, 'GET', $params);
    }

    /**
     * @dataProvider getPages
     * @phpstan-param array<scalar> $params
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

    /**
     * @phpstan-param array<scalar> $params
     * @phpstan-return array{string,string,array<scalar>}
     */
    protected function transformParams(string $presenterName, string $action, array $params): array
    {
        return [$presenterName, $action, $params];
    }

    /**
     * @phpstan-return array<array{string,string}>
     */
    abstract public function getPages(): array;
}
