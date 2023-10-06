<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Schedule\Input\ScheduleContainer;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\Fyziklani\TeamTeacherService;
use FKSDB\Models\Persons\Resolvers\SelfACLResolver;
use Nette\Forms\Control;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Neon\Exception;
use Nette\Neon\Neon;

/**
 * @phpstan-import-type EvaluatedFieldMetaData from ReferencedPersonContainer
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
class FOFTeamFormComponent extends TeamFormComponent
{

    private TeamTeacherService $teacherService;

    final public function injectSecondary(TeamTeacherService $teacherService): void
    {
        $this->teacherService = $teacherService;
    }

    /**
     * @throws Exception
     * @phpstan-return EvaluatedFieldsDefinition
     */
    protected function getMemberFieldsDefinition(): array
    {
        return Neon::decodeFile(__DIR__ . DIRECTORY_SEPARATOR . 'fof.member.neon');
    }

    /**
     * @throws Exception
     * @phpstan-return EvaluatedFieldsDefinition
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
        $this->appendTeacherFields($form);
        $this->appendMemberFields($form);
        foreach ($form->getComponents(true, ReferencedContainer::class) as $component) {
            /** @var BaseControl $genderField */
            $genderField = $component['person']['gender'];//@phpstan-ignore-line
            /** @var BaseControl $idNumberField */
            $idNumberField = $component['person_info']['id_number'];//@phpstan-ignore-line
            /** @var ScheduleContainer $accommodationField */
            $accommodationField = $component['person_schedule']['accommodation'];//@phpstan-ignore-line
            /** @var BaseControl $bornField */
            $bornField = $component['person_info']['born'];//@phpstan-ignore-line
            /** @var Control $baseComponent */
            foreach ($accommodationField->getComponents() as $baseComponent) {
                $genderField->addConditionOn($baseComponent, Form::FILLED)
                    ->addRule(Form::FILLED, _('Field %label is required.'));
                $genderField->addConditionOn($baseComponent, Form::FILLED)
                    ->toggle($genderField->getHtmlId() . '-pair');
                $idNumberField->addConditionOn($baseComponent, Form::FILLED)
                    ->addRule(Form::FILLED, _('Field %label is required.'));
                $idNumberField->addConditionOn($baseComponent, Form::FILLED)
                    ->toggle($idNumberField->getHtmlId() . '-pair');
                $bornField->addConditionOn($baseComponent, Form::FILLED)
                    ->addRule(Form::FILLED, _('Field %label is required.'));
                $bornField->addConditionOn($baseComponent, Form::FILLED)
                    ->toggle($bornField->getHtmlId() . '-pair');
            }
        }
    }

    /**
     * @phpstan-return FormProcessing[]
     */
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
        /** @var TeamTeacherModel $oldTeacher */
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
    private function appendTeacherFields(Form $form): void
    {
        $teacherCount = isset($this->model) ? max($this->model->getTeachers()->count('*'), 1) : 1;

        for ($teacherIndex = 0; $teacherIndex < $teacherCount; $teacherIndex++) {
            $teacherContainer = $this->referencedPersonFactory->createReferencedPerson(
                $this->getTeacherFieldsDefinition(),
                $this->event->getContestYear(),
                'email',
                true,
                new SelfACLResolver(
                    $this->model ?? TeamModel2::RESOURCE_ID,
                    'organizer',
                    $this->event->event_type->contest,
                    $this->container
                ),
                $this->event
            );
            $teacherContainer->searchContainer->setOption('label', sprintf(_('Teacher #%d'), $teacherIndex + 1));
            $teacherContainer->referencedContainer->setOption('label', sprintf(_('Teacher #%d'), $teacherIndex + 1));
            $form->addComponent($teacherContainer, 'teacher_' . $teacherIndex);
        }
    }

    /**
     * @phpstan-return array{
     *     name:EvaluatedFieldMetaData,
     *     game_lang:EvaluatedFieldMetaData,
     *     phone:EvaluatedFieldMetaData,
     *     force_a:EvaluatedFieldMetaData,
     * }
     */
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
     * @phpstan-return PersonModel[]
     */
    public static function getTeacherFromForm(Form $form): array
    {
        $persons = [];
        $teacherIndex = 0;
        while (true) {
            /** @phpstan-var ReferencedId<PersonModel>|null $referencedId */
            $referencedId = $form->getComponent('teacher_' . $teacherIndex, false);
            if (!$referencedId) {
                break;
            }
            $person = $referencedId->getModel();
            if ($person) {
                $persons[$person->person_id] = $person;
            }
            $teacherIndex++;
        }
        return $persons;
    }

    protected function setDefaults(Form $form): void
    {
        parent::setDefaults($form);
        if (isset($this->model)) {
            $index = 0;
            /** @var TeamTeacherModel $teacher */
            foreach ($this->model->getTeachers() as $teacher) {
                /** @phpstan-var ReferencedId<PersonModel> $referencedId */
                $referencedId = $form->getComponent('teacher_' . $index);
                $referencedId->setDefaultValue($teacher->person);
                $index++;
            }
        }
    }
}
