<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests;

use FKSDB\Models\Authorization\Roles\Base\ExplicitBaseRole;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\Grant\BaseGrantService;
use FKSDB\Models\ORM\Services\Grant\ContestGrantService;
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
    protected LoginModel $cartesianLogin;
    protected Presenter $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->createPresenter($this->getPresenterName());
    }

    /**
     * @phpstan-param array<string,scalar> $params
     * @phpstan-param array<string,scalar> $postData
     */
    protected function createPostRequest(string $action, array $params, array $postData = []): Request
    {
        $params['lang'] = 'en';
        $params['action'] = $action;
        return new Request($this->getPresenterName(), 'POST', $params, $postData);
    }

    /**
     * @phpstan-param array<string,scalar> $params
     * @phpstan-param array<string,scalar> $postData
     */
    protected function createGetRequest(string $action, array $params, array $postData = []): Request
    {
        $params['lang'] = 'en';
        $params['action'] = $action;
        return new Request($this->getPresenterName(), 'GET', $params, $postData);
    }

    protected function loginUser(string $roleId = ExplicitBaseRole::Cartesian): void
    {
        $this->cartesianPerson = $this->container->getByType(PersonService::class)->storeModel([
            'family_name' => 'Cartesian',
            'other_name' => 'Cartesiansky',
            'gender' => 'M',
        ]);
        $this->cartesianLogin = $this->container->getByType(LoginService::class)->storeModel(
            ['person_id' => $this->cartesianPerson->person_id, 'active' => 1]
        );
        if ($roleId === ExplicitBaseRole::Cartesian) {
            $this->container->getByType(BaseGrantService::class)->storeModel(
                ['login_id' => $this->cartesianLogin->login_id, 'role' => $roleId]
            );
        } else {
            $this->container->getByType(ContestGrantService::class)->storeModel(
                ['login_id' => $this->cartesianLogin->login_id, 'role' => $roleId, 'contest_id' => 1]
            );
        }
        $this->authenticateLogin($this->cartesianLogin, $this->fixture);
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

    /**
     * @phpstan-param array<string,scalar> $params
     * @phpstan-param array<string,mixed> $formData
     */
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

    public static function personToValues(ContestModel $contest, PersonModel $person): array
    {
        $history = $person->getHistory($contest->getCurrentContestYear());
        return [
            '_c_compact' => $person->getFullName(),
            'person' => [
                'other_name' => $person->other_name,
                'family_name' => $person->family_name,
                'gender' => $person->gender->value,
            ],
            'person_info' => [
                'email' => $person->getInfo()->email,
                'born' => $person->getInfo()->born ? $person->getInfo()->born->format('c') : null,
            ],
            'person_history' => $history ? [
                'school_id__meta' => (string)$history->school_id,
                'school_id' => (string)$history->school_id,
                'study_year_new' => $history->study_year_new->value,
            ] : [],
            'person_has_flag' => [
                'spam_mff' => '1',
            ],
        ];
    }

    abstract protected function getPresenterName(): string;
}
