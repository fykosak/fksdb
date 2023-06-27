<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

class FOLTeamFormComponent extends TeamFormComponent
{


    protected function getProcessing(): array
    {
        return [
            new FOLCategoryProcessing($this->container),
        ];
    }

    public function render(): void
    {
        $this->template->event = $this->event;
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.fol.latte';
    }

    protected function getMemberFieldsDefinition(): array
    {
        return [
            'person' => [
                'other_name' => [
                    'required' => true,
                    'description' => _(
                        'Usually the first part of your name. For example, "Albert".'
                    ),
                ],
                'family_name' => [
                    'required' => true,
                    'description' => _(
                        'The second part of your name. For example, "Einstein".'
                    ),
                ],
            ],
            'person_info' => [
                'email' => [
                    'required' => true,
                ],
                'born' => [
                    'required' => false,
                    'description' => _('Only for Czech and Slovak students'),
                ],
            ],
            'person_history' => [
                'school_id' => [
                    'required' => true,
                    'description' => _(
                        'Napište prvních několik znaků vaší školy, školu pak vyberete ze seznamu. 
                        Pokud nelze školu nalézt, pošlete na email schola.novum@fykos.cz údaje o vaší škole jako název,
                        adresu a pokud možno i odkaz na webovou stránku.
                        Školu založíme a pošleme vám odpověď. Pak budete schopni dokončit 
                        registraci. Pokud nejste student, vyplňte "not a student".'
                    ),
                ],
                'study_year' => [
                    'required' => false,
                    'description' => _('Pro výpočet kategorie. Ponechte nevyplněné, pokud nejste ze SŠ/ZŠ.'),
                ],
            ],
            'person_has_flag' => [
                'spam_mff' => [
                    'required' => false,
                    'description' => _('Pouze pro české a slovenské studenty.'),
                ],
            ],
        ];
    }

    protected function getTeamFieldsDefinition(): array
    {
        return ['name' => ['required' => true]];
    }

    protected function getTeacherFieldsDefinition(): array
    {
        return [];
    }
}
