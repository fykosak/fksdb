<?php

$container = require '../bootstrap.php';

use Events\Spec\Fol\CategoryProcessing;
use Nette\Application\Request;
use Tester\Assert;

class ApplicationPresenterTest extends ApplicationPresenterFolTestCase {

    public function testDisplay() {
        $request = new Request('Public:Application', 'GET', array(
            'action' => 'default',
            'lang' => 'en',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\TextResponse', $response);

        $source = $response->getSource();
        Assert::type('Nette\Templating\ITemplate', $source);

        $html = (string) $source;
        Assert::contains('Register team', $html);
    }

    public function testAnonymousRegistration() {
        $request = $this->createPostRequest(array(
            'team' => array(
                'name' => 'Okurkový tým',
                'password' => '1234',
            ),
            'p1' => array(
                'person_id' => "__promise",
                'person_id_1' => array(
                    '_c_compact' => " ",
                    'person' => array(
                        'other_name' => "František",
                        'family_name' => "Dobrota",
                    ),
                    'person_info' => array(
                        'email' => "ksaad@kalo.cz",
                    ),
                    'person_history' => array(
                        'school_id__meta' => 'JS',
                        'school_id' => 1,
                        'study_year' => '',
                    ),
                ),
            ),
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            '__init__applied' => "Apply team",
        ));

        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\RedirectResponse', $response);

        $teamApplication = $this->assertTeamApplication($this->eventId, 'Okurkový tým');
        Assert::equal(sha1('1234'), $teamApplication->password);
        Assert::equal(CategoryProcessing::OPEN, $teamApplication->category);

        $application = $this->assertApplication($this->eventId, 'ksaad@kalo.cz');
        Assert::equal('applied', $application->status);

        $eApplication = $this->assertExtendedApplication($application, 'e_fyziklani_participant');
        Assert::equal($teamApplication->e_fyziklani_team_id, $eApplication->e_fyziklani_team_id);
    }

}

$testCase = new ApplicationPresenterTest($container);
$testCase->run();
