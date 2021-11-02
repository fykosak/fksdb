<?php

declare(strict_types=1);

namespace FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\FOL;

$container = require '../../../../Bootstrap.php';

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Tests\PresentersTests\PublicModule\ApplicationPresenter\FolTestCase;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Template;
use Tester\Assert;

class AnonymousTest extends FolTestCase
{

    public function testDisplay(): void
    {
        $request = new Request('Public:Application', 'GET', [
            'action' => 'default',
            'lang' => 'en',
            'contestId' => 1,
            'year' => 1,
            'eventId' => $this->eventId,
        ]);

        $response = $this->fixture->run($request);
        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $source = $response->getSource();
        Assert::type(Template::class, $source);

        $html = (string)$source;
        Assert::contains('Register team', $html);
    }

    public function testAnonymousRegistration(): void
    {
        $request = $this->createPostRequest([
            'team' => [
                'name' => 'Okurkový tým',
                'password' => '1234',
            ],
            'p1' => [
                'person_id' => "__promise",
                'person_id_1' => [
                    '_c_compact' => " ",
                    'person' => [
                        'other_name' => "František",
                        'family_name' => "Dobrota",
                    ],
                    'person_info' => [
                        'email' => "ksaad@kalo.cz",
                    ],
                    'person_history' => [
                        'school_id__meta' => 'JS',
                        'school_id' => (string)1,
                        'study_year' => '',
                    ],
                ],
            ],
            'privacy' => "on",
            'c_a_p_t_cha' => "pqrt",
            '__init__applied' => "Apply team",
        ]);

        $response = $this->fixture->run($request);
        Assert::type(RedirectResponse::class, $response);

        $teamApplication = $this->assertTeamApplication($this->eventId, 'Okurkový tým');
        Assert::equal(sha1('1234'), $teamApplication->password);
        Assert::equal(ModelFyziklaniTeam::CATEGORY_OPEN, $teamApplication->category);

        $application = $this->assertApplication($this->eventId, 'ksaad@kalo.cz');
        Assert::equal('applied', $application->status);

        $eApplication = $this->assertExtendedApplication($application, 'e_fyziklani_participant');
        Assert::equal($teamApplication->e_fyziklani_team_id, $eApplication->e_fyziklani_team_id);
    }
}

$testCase = new AnonymousTest($container);
$testCase->run();
