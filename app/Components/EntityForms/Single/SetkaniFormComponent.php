<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Single;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Forms\Form;
use Nette\Neon\Exception;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
final class SetkaniFormComponent extends SingleFormComponent
{

    private PersonService $personService;

    public function injectPersonService(PersonService $personService): void {
        $this->personService = $personService;
    }

    /**
     * @throws Exception
     * @phpstan-return EvaluatedFieldsDefinition
     */
    protected function getPersonFieldsDefinition(): array
    {
        return [
            'person' => [
                'other_name' => ['required' => true],
                'family_name' => ['required' => true]
            ],
            'person_info' => [
                'email' => ['required' => true],
                'born' => ['required' => true],
                'id_number' => [
                    'required' => false,
                    'description' => _('Číslo OP/pasu, pokud máš')
                ],
                'phone_parent_m' => ['required' => false],
                'phone_parent_d' => ['required' => false],
                'phone' => ['required' => true]
            ],
            'person_schedule' => [
                'apparel' => ['required' => true],
                'transport' => ['required' => true],
                'ticket' => ['required' => true]
            ]
        ];
    }

    /**
     * @phpstan-return array<string, array<string, mixed>>
     */
    protected function getParticipantFieldsDefinition(): array
    {
        return [
            'diet' => [
                'required' => false,
                'description' => _("Máš nějaké speciální stravovací návyky – vegetariánství, veganství, diety, …?
                Pokud ano, máš zájem o speciální stravu nebo si (zejména v případě veganů) dovezeš jídlo vlastní?")
            ],
            'health_restrictions' => [
                'required' => false,
                'description' => _("Máš nějaká zdravotní omezení, která by tě mohla omezovat v pobytu na setkání?
                Například různé alergie (a jejich projevy), cukrovka, epilepsie, dlouhodobější obtíže, … Bereš
                nějaké léky, ať už pravidelně, nebo v případě obtíží? Jaké to jsou? Jsou nějaké další informace
                ohledně tvého zdravotního stavu, co bychom měli vědět?")],
            'note' => ['required' => false]
        ];
    }

    /**
     * @throws \Throwable
     */
    protected function handleFormSuccess(Form $form): void
    {
        /** @var array<string,mixed> $values */
        $values = $form->getValues('array');

        /** @var PersonModel|Null */
        $personModel = $this->personService->findByPrimary($values['person_id']);

        if (!$personModel) {
            throw new NotContestantException();
        }

        /** @var int $contestYear */
        $contestYear = $this->event->getContestYear()->year;

        /** @var array<ContestantModel> $contestantModels */
        $contestantModels = $personModel->getContestants($this->event->event_type->contest)->where('year', [$contestYear, $contestYear-1]);

        if(count($contestantModels) == 0) {
            throw new NotContestantException();
        }

        $submitsCount = 0;
        foreach ($contestantModels as $contestant) {
            $submitsCount += $contestant->getSubmits()->count();
        }

        if ($submitsCount == 0) {
            throw new NotContestantException();
        }

        parent::handleFormSuccess($form);
    }
}
