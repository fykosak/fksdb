<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\Fyziklani\TeamTeacherService;
use FKSDB\Models\Persons\Resolvers\SelfACLResolver;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Neon\Exception;
use Nette\Neon\Neon;

class FOFTeamFormComponent extends TeamFormComponent
{

    private TeamTeacherService $teacherService;

    final public function injectSecondary(TeamTeacherService $teacherService): void
    {
        $this->teacherService = $teacherService;
    }

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

    /**
     * @throws Exception
     */
    protected function appendPersonsFields(Form $form): void
    {
        $this->appendTeacherField($form);
        $this->appendMemberFields($form);
    }

    protected function appendMemberFields(Form $form): void
    {
        parent::appendMemberFields($form);
        foreach ($form->getComponents(true, ReferencedContainer::class) as $component) {
            /** @var BaseControl $genderField */
            $genderField = $component['person']['gender'];
            /** @var BaseControl $idNumberField */
            $idNumberField = $component['person_info']['id_number'];
            /** @var BaseControl $accommodationField */
            $accommodationField = $component['person_schedule']['accommodation'];
            $genderField->addConditionOn($accommodationField, Form::FILLED)
                ->addRule(Form::FILLED, _('Field %label is required.'));
            $genderField->addConditionOn($accommodationField, Form::FILLED)
                ->toggle($genderField->getHtmlId() . '-pair');
            $idNumberField->addConditionOn($accommodationField, Form::FILLED)
                ->addRule(Form::FILLED, _('Field %label is required.'));
            $idNumberField->addConditionOn($accommodationField, Form::FILLED)
                ->toggle($idNumberField->getHtmlId() . '-pair');
        }
    }

    protected function getProcessing(): array
    {
        return [
            new FOFCategoryProcessing($this->container),
            new SchoolsPerTeamProcessing($this->container),
        ];
    }

    private function saveTeachers(TeamModel2 $team, Form $form): void
    {
        $persons = self::getTeacherFromForm($form);

        $oldMemberQuery = $team->getTeachers();
        if (count($persons)) {
            $oldMemberQuery->where('person_id NOT IN', array_keys($persons));
        }
        /** @var TeamMemberModel $oldTeacher */
        foreach ($oldMemberQuery as $oldTeacher) {
            $this->teacherService->disposeModel($oldTeacher);
        }
        foreach ($persons as $person) {
            $oldTeacher = $team->getTeachers()->where('person_id', $person->person_id)->fetch();
            if (!$oldTeacher) {
                $this->teacherService->storeModel([
                    'person_id' => $person->getPrimary(),
                    'fyziklani_team_id' => $team->fyziklani_team_id,
                ]);
            }
        }
    }

    protected function savePersons(TeamModel2 $team, Form $form): void
    {
        $this->saveTeachers($team, $form);
        $this->saveMembers($team, $form);
    }

    /**
     * @throws Exception
     */
    private function appendTeacherField(Form $form): void
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

    protected function getTeamFieldsDefinition(): array
    {
        return [
            'name' => ['required' => true],
            'game_lang' => ['required' => true],
            'phone' => ['required' => true],
            'force_a' => ['required' => false],
        ];
    }

    /**
     * @return PersonModel[]
     */
    public static function getTeacherFromForm(Form $form): array
    {
        /** @var ReferencedId $referencedId */
        $referencedId = $form->getComponent('teacher');
        if (!$referencedId) {
            return [];
        }
        /** @var PersonModel $person */
        $person = $referencedId->getModel();
        return $person ? [$person->person_id => $person] : [];
    }

    protected function setDefaults(): void
    {
        parent::setDefaults();
        if (isset($this->model)) {
            $teacher = $this->model->getTeachers()->fetch();
            /** @var TeamTeacherModel $teacher */
            if ($teacher) {
                /** @var ReferencedId $referencedId */
                $referencedId = $this->getForm()->getComponent('teacher');
                $referencedId->setDefaultValue($teacher->person);
            }
        }
    }
}
