<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Tests\MockEnvironment\MockApplicationTrait;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Nette\DI\Container;
use Tester\Assert;

/**
 * Class EntityPresenterTestCase
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class EntityPresenterTestCase extends DatabaseTestCase
{
    use MockApplicationTrait;

    protected int $cartesianPersonId;
    protected int $loginId;
    protected Presenter $fixture;

    /**
     * OrgPresenter constructor.
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
        $this->cartesianPersonId = $this->insert(DbNames::TAB_PERSON, [
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);
        $this->loginId = $this->insert(DbNames::TAB_LOGIN, ['person_id' => $this->cartesianPersonId, 'active' => 1]);

        $this->insert(DbNames::TAB_GRANT, ['login_id' => $this->loginId, 'role_id' => $roleId, 'contest_id' => 1]);
        $this->authenticate($this->loginId, $this->fixture);
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
                '_do' => ($action === 'create') ? 'createForm-formControl-form-submit'
                    : 'editForm-formControl-form-submit',
                'send' => 'Save',
            ], $formData)
        );

        return $this->fixture->run($request);
    }

    abstract protected function getPresenterName(): string;
}
