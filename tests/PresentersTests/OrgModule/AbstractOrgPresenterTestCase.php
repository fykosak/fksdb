<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\OrgModule;

use FKSDB\Tests\PresentersTests\EntityPresenterTestCase;
use Nette\Application\Request;

abstract class AbstractOrgPresenterTestCase extends EntityPresenterTestCase
{
    protected function createPostRequest(string $action, array $params, array $postData = []): Request
    {
        $params['year'] = 1;
        $params['contestId'] = 1;
        $params['series'] = 1;
        return parent::createPostRequest($action, $params, $postData);
    }

    protected function createGetRequest(string $action, array $params, array $postData = []): Request
    {
        $params['year'] = 1;
        $params['contestId'] = 1;
        $params['series'] = 1;
        return parent::createGetRequest($action, $params, $postData);
    }
}
