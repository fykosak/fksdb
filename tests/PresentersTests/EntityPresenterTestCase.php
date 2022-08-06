<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests;

use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\GrantService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Tester\Assert;

abstract class EntityPresenterTestCase extends DatabaseTestCase
{

    protected PersonModel $cartesianPerson;
    protected LoginModel $login;
    protected Presenter $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->createPresenter($this->getPresenterName());
    }

    protected function createPostRequest(string $action, array $params, array $postData = []): Request
    {
        $params['lang'] = 'en';
        $params['action'] = $action;
        return new Request($this->getPresenterName(), 'POST', $params, $postData);
    }

    protected function createGetRequest(string $action, array $params, array $postData = []): Request
    {
        $params['lang'] = 'en';
        $params['action'] = $action;
        return new Request($this->getPresenterName(), 'GET', $params, $postData);
    }

    protected function loginUser(int $roleId = 1000): void
    {
        $this->cartesianPerson = $this->getContainer()->getByType(PersonService::class)->createNewModel([
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);
        $this->login = $this->getContainer()->getByType(LoginService::class)->createNewModel(
            ['person_id' => $this->cartesianPerson->person_id, 'active' => 1]
        );

        $this->getContainer()->getByType(GrantService::class)->createNewModel(
            ['login_id' => $this->login->login_id, 'role_id' => $roleId, 'contest_id' => 1]
        );
        $this->authenticateLogin($this->login, $this->fixture);
    }

    protected function assertPageDisplay(Response $response): string
    {
        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $source = $response->getSource();
        Assert::type(Template::class, $source);

        Assert::noError(function () use ($source): string {
            return (string)$source;
        });
        return (string)$source;
    }

    protected function createFormRequest(string $action, array $formData, array $params = []): Response
    {
        $request = $this->createPostRequest(
            $action,
            $params,
            array_merge([
                '_do' => ($action === 'create')
                    ? 'createForm-formControl-form-submit'
                    : 'editForm-formControl-form-submit',
                'send' => 'Save',
            ], $formData)
        );

        return $this->fixture->run($request);
    }

    abstract protected function getPresenterName(): string;
}
