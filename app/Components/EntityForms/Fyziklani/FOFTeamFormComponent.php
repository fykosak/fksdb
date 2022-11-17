<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\Forms\FormProcessing\FOFCategoryProcessing;
use FKSDB\Components\Forms\FormProcessing\SchoolsPerTeamProcessing;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\Persons\Resolvers\SelfACLResolver;
use Nette\Forms\Form;
use Nette\Neon\Exception;
use Nette\Neon\Neon;

class FOFTeamFormComponent extends TeamFormComponent
{
    /**
     * @throws Exception
     */
    protected function getMemberFieldsDefinition(): array
    {
        return Neon::decodeFile(__DIR__ . DIRECTORY_SEPARATOR . 'fof.member.neon');
    }

    /**
     * @throws Exception
     */
    protected function getTeacherFieldsDefinition(): array
    {
        return Neon::decodeFile(__DIR__ . DIRECTORY_SEPARATOR . 'fof.teacher.neon');
    }

    protected function getProcessing(): array
    {
        return [
            new FOFCategoryProcessing($this->container),
            new SchoolsPerTeamProcessing($this->container),
        ];
    }

    /**
     * @throws Exception
     */
    protected function appendTeacherField(Form $form): void
    {
        $teacherContainer = $this->referencedPersonFactory->createReferencedPerson(
            $this->getTeacherFieldsDefinition(),
            $this->event->getContestYear(),
            'email',
            true,
            new SelfACLResolver(
                $this->model ?? TeamModel2::RESOURCE_ID,
                $this->model ? 'org-edit' : 'org-create',
                $this->event->event_type->contest,
                $this->container
            ),
            $this->event
        );
        $teacherContainer->searchContainer->setOption('label', _('Teacher'));
        $teacherContainer->referencedContainer->setOption('label', _('Teacher'));
        $form->addComponent($teacherContainer, 'teacher');
    }

    protected function getTeamFields(): array
    {
        return ['name', 'game_lang', 'phone', 'force_a'];
    }

}
