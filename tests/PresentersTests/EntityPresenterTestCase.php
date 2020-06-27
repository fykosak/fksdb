<?php

namespace FKSDB\Tests\PresentersTests;

use FKSDB\Modules\OrgModule\OrgPresenter;
use FKSDB\ORM\DbNames;
use FKSDB\Tests\ModelTests\DatabaseTestCase;
use MockEnvironment\MockApplicationTrait;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplate;
use Nette\DI\Container;
use Tester\Assert;

abstract class EntityPresenterTestCase extends DatabaseTestCase {
    use MockApplicationTrait;

    /** @var int */
    protected $cartesianPersonId;
    /** @var OrgPresenter */
    protected $fixture;

    /**
     * OrgPresenter constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();
        $this->fixture = $this->createPresenter($this->getPresenterName());
    }

    protected function createPostRequest(string $action, array $params, array $postData = []): Request {
        $params['lang'] = 'en';
        $params['action'] = $action;
        return new Request($this->getPresenterName(), 'POST', $params, $postData);
    }

    protected function createGetRequest(string $action, array $params, array $postData = []): Request {
        $params['lang'] = 'en';
        $params['action'] = $action;
        return new Request($this->getPresenterName(), 'GET', $params, $postData);
    }

    protected function loginUser(int $roleId = 1000) {
        $this->cartesianPersonId = $this->insert(DbNames::TAB_PERSON, [
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);
        $loginId = $this->insert(DbNames::TAB_LOGIN, ['person_id' => $this->cartesianPersonId, 'active' => 1]);

        $this->insert(DbNames::TAB_GRANT, ['login_id' => $loginId, 'role_id' => $roleId, 'contest_id' => 1]);
        $this->authenticate($loginId);
    }

    protected function assertPageDisplay(IResponse $response): string {
        Assert::type(TextResponse::class, $response);
        $source = $response->getSource();
        Assert::type(ITemplate::class, $source);

        Assert::noError(function () use ($source) {
            return (string)$source;
        });
        return (string)$source;
    }

    protected function createFormRequest(string $action, array $formData, array $params = []): IResponse {
        $request = $this->createPostRequest($action, $params, array_merge([
            '_do' => ($action === 'create') ? 'createForm-formControl-form-submit' : 'editForm-formControl-form-submit',
            '_submit' => 'save',
        ], [$this->getContainerName() => $formData]));
        return $this->fixture->run($request);
    }

    abstract protected function getPresenterName(): string;

    abstract protected function getContainerName(): string;
}
