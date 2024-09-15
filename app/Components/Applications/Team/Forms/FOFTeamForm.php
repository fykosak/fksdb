<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team\Forms;

use FKSDB\Components\Applications\Team\Forms\Processing\Category\FOFCategoryProcessing;
use FKSDB\Components\Applications\Team\Forms\Processing\SchoolsPerTeam\SchoolsPerTeam;
use FKSDB\Components\Applications\Team\Forms\Processing\SendInfoEmail;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\Models\ReferencedContainer;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Schedule\Input\ScheduleContainer;
use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Services\Fyziklani\TeamTeacherService;
use FKSDB\Models\Persons\Resolvers\SelfACLResolver;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * @phpstan-import-type EvaluatedFieldMetaData from ReferencedPersonContainer
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
class FOFTeamForm extends TeamForm
{
    private TeamTeacherService $teacherService;

    final public function injectSecondary(TeamTeacherService $teacherService): void
    {
        $this->teacherService = $teacherService;
    }

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
            /** @var ContainerWithOptions $dayContainer */
            foreach ($accommodationField->getComponents() as $dayContainer) {
                /** @var BaseControl $baseComponent */
                foreach ($dayContainer->getComponents() as $baseComponent) {
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
    }

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
                    $this->model ?? new PseudoEventResource(TeamModel2::RESOURCE_ID, $this->event),
                    'organizer',
                    $this->event->event_type->contest,
                    $this->container
                ),
                $this->event
            );
            $teacherContainer->searchContainer->setOption('label', self::formatTeacherLabel($teacherIndex + 1));
            $teacherContainer->referencedContainer->setOption('label', self::formatTeacherLabel($teacherIndex + 1));
            $form->addComponent($teacherContainer, 'teacher_' . $teacherIndex);
        }
    }

    /**
     * @phpstan-return EvaluatedFieldsDefinition
     */
    protected function getTeacherFieldsDefinition(): array
    {
        return [
            'person' => [
                'other_name' => ['required' => true],
                'family_name' => ['required' => true],
                'gender' => ['required' => false],
            ],
            'person_info' => [
                'email' => ['required' => true],
                'born' => ['required' => false],
                'id_number' => ['required' => false],
                'academic_degree_prefix' => ['required' => false],
                'academic_degree_suffix' => ['required' => false],
            ],
            'person_schedule' => [
                'accommodation' => [
                    'types' => [ScheduleGroupType::Accommodation, ScheduleGroupType::AccommodationTeacher],
                    'required' => false,
                    'collapseSelf' => true,
                    'label' => _('Accommodation'),
                    'groupBy' => ScheduleContainer::GROUP_NONE,
                ],
                'schedule' => [
                    'types' => [
                        ScheduleGroupType::TeacherPresent,
                        ScheduleGroupType::Weekend,
                        ScheduleGroupType::WeekendInfo,
                    ],
                    'required' => false,
                    'collapseChild' => true,
                    'label' => _('Schedule'),
                    'groupBy' => ScheduleContainer::GROUP_DATE,
                ],
            ],
        ];
    }

    /**
     * @phpstan-return EvaluatedFieldsDefinition
     */
    protected function getMemberFieldsDefinition(): array
    {
        return [
            'person' => [
                'other_name' => ['required' => true],
                'family_name' => ['required' => true],
                'gender' => ['required' => false],
            ],
            'person_info' => [
                'email' => ['required' => true],
                'born' => ['required' => false],
                'id_number' => ['required' => false],
            ],
            'person_history' => [
                'school_id' => ['required' => true],
                'study_year_new' => [
                    'required' => true,
                    'flag' => 'ALL',
                ],
            ],
            'person_schedule' => [
                'accommodation' => [
                    'types' => [ScheduleGroupType::Accommodation, ScheduleGroupType::AccommodationGender],
                    'required' => false,
                    'collapseSelf' => true,
                    'label' => _('Accommodation'),
                    'groupBy' => ScheduleContainer::GROUP_NONE,
                ],
                'schedule' => [
                    'types' => [ScheduleGroupType::Weekend, ScheduleGroupType::WeekendInfo],
                    'required' => false,
                    'collapseChild' => true,
                    'label' => _('Schedule'),
                    'groupBy' => ScheduleContainer::GROUP_DATE,
                ],
            ],
        ];
    }

    protected function savePersons(TeamModel2 $team, Form $form): void
    {
        $this->saveTeachers($team, $form);
        parent::savePersons($team, $form);
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
    /**
     * @phpstan-return array{
     *     name:EvaluatedFieldMetaData,
     *     game_lang:EvaluatedFieldMetaData,
     *     phone:EvaluatedFieldMetaData,
     * }
     */
    protected function getTeamFieldsDefinition(): array
    {
        return [
            'name' => ['required' => true],
            'game_lang' => ['required' => true],
            'phone' => ['required' => true],
            'origin' => ['required' => false],
        ];
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
                $referencedId->referencedContainer->setOption(
                    'label',
                    self::formatTeacherLabel($index + 1, $teacher)
                );
                $referencedId->searchContainer->setOption(
                    'label',
                    self::formatTeacherLabel($index + 1, $teacher)
                );
                $index++;
            }
        }
    }

    protected function getPreprocessing(): array
    {
        $processing = parent::getPreprocessing();
        $processing[] = new FOFCategoryProcessing($this->container, $this->event);
        $processing[] = new SchoolsPerTeam($this->container, $this->event);
        return $processing;
    }

    protected function getPostprocessing(): array
    {
        $postprocessing = parent::getPostprocessing();
        if (isset($this->model)) {
            // pri každej editácii okrem initu pošle mail
            $postprocessing[] = new SendInfoEmail($this->container, $this->machine);
        }
        return $postprocessing;
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

    public static function formatTeacherLabel(int $index, ?TeamTeacherModel $teacher = null): string
    {
        if ($teacher) {
            return sprintf(_('Teacher %d - %s'), $index, $teacher->person->getFullName());
        } else {
            return sprintf(_('Teacher %d'), $index);
        }
    }
}
