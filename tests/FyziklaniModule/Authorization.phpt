<?php

namespace FyziklaniModule;

$container = require '../bootstrap.php';

use DbNames;
use FyziklaniModule\FyziklaniTestCase;
use MockEnvironment\MockApplicationTrait;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Request;
use Nette\DateTime;
use Nette\DI\Container;
use Tester\Assert;

class AuthorizationTest extends FyziklaniTestCase {

    use MockApplicationTrait;

    private $perPerson;

    private $perOrg;

    private $perOrgOther;

    private $perContestant;

    function __construct(Container $container) {
        parent::__construct($container);
        $this->setContainer($container);
    }

    protected function setUp() {
        parent::setUp();

        $this->perPerson = $this->createPerson('Karkulka', 'Červená', array(
            'email' => 'karkulka@les.cz', 'born' => DateTime::from('2000-01-01')
                ), true);

        $this->perOrg = $this->createPerson('Karkulka', 'Červená', array(
            'email' => 'karkulka2@les.cz', 'born' => DateTime::from('2000-01-01')
                ), true);
        $this->insert(DbNames::TAB_ORG, array('person_id' => $this->perOrg, 'contest_id' => 1, 'since' => 0, 'order' => 0));

        $this->perOrgOther = $this->createPerson('Karkulka', 'Červená', array(
            'email' => 'karkulka3@les.cz', 'born' => DateTime::from('2000-01-01')
                ), true);
        $this->insert(DbNames::TAB_ORG, array('person_id' => $this->perOrgOther, 'contest_id' => 2, 'since' => 0, 'order' => 0));

        $this->perContestant = $this->createPerson('Karkulka', 'Červená', array(
            'email' => 'karkulka4@les.cz', 'born' => DateTime::from('2000-01-01')
                ), true);
        $this->insert(DbNames::TAB_CONTESTANT_BASE, array('person_id' => $this->perContestant, 'contest_id' => 1, 'year' => 1));

        $this->eventId = $this->createEvent(array());


        $this->mockApplication();
        $this->container->parameters[BasePresenter::EVENT_NAME][$this->eventId] = &$this->container->parameters[BasePresenter::EVENT_NAME][0];
    }

    protected function tearDown() {
        $this->connection->query("DELETE FROM contestant_base");

        parent::tearDown();
    }

    public function getTestData() {
        return [
                [null, 'Fyziklani:Submit', ['entry', 'edit', 'table'], false],
                ['perPerson', 'Fyziklani:Submit', ['entry', 'edit', 'table'], false],
                ['perOrg', 'Fyziklani:Submit', ['entry', 'edit', 'table'], true],
                ['perOrgOther', 'Fyziklani:Submit', ['entry', 'edit', 'table'], false],
                ['perContestant', 'Fyziklani:Submit', ['entry', 'edit', 'table'], false],
        ];
    }

    private function createGetRequest($presenterName, $action) {
        $params = array(
            'lang' => 'cs',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
            'action' => $action,
            'id' => 'dummy',
        );

        $request = new Request($presenterName, 'GET', $params);
        return $request;
    }

    /**
     * @dataProvider getTestData
     */
    public function testAccess($personId, $presenterName, $actions, $results) {
        if (!is_array($actions)) {
            $actions = array($actions);
        }
        if (!is_array($results)) {
            $results = array_fill(0, count($actions), $results);
        }
        if ($personId) {
            /* Use indirect access because data provider is called before test set up. */
            $this->authenticate($this->{$personId});
        }

        $presenter = $this->createPresenter($presenterName);
        foreach ($actions as $i => $action) {
            $request = $this->createGetRequest($presenterName, $action);
            $forbidden = false;
            $response = null;
            try {
                $response = $presenter->run($request);
            } catch (ForbiddenRequestException $e) {
                $forbidden = true;
                $response = $e->getCode();
            } catch (\Nette\Application\BadRequestException $e) {
                $forbidden = ($e->getCode() == 403);
                $response = $e->getCode();
            }
            if ($results[$i]) {
                if (is_object($response)) {
                    Assert::type('Nette\Application\Responses\TextResponse', $response);
                } else {
                    Assert::notSame(403, $response);
                }
            } else {
                if (!$forbidden) {
                    Assert::type('Nette\Application\Responses\RedirectResponse', $response);
                    $url = $response->getUrl();
                    Assert::contains('login', $url);
                }
            }
        }
    }

}

$testCase = new AuthorizationTest($container);
$testCase->run();
