<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\Persons\SelfResolver;
use FKSDB\Models\Transitions\FormAdjustment\FormAdjustment;
use FKSDB\Models\Transitions\Machine\FyziklaniTeamMachine;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Security\User;

class TeamApplicationFormComponent extends EntityFormComponent
{
    private SingleReflectionFormFactory $reflectionFormFactory;
    private FyziklaniTeamMachine $machine;
    /** @var FormAdjustment[] */
    private array $adjustment;
    private ReferencedPersonFactory $referencedPersonFactory;
    private EventModel $event;
    private User $user;

    public function __construct(
        FyziklaniTeamMachine $machine,
        EventModel $event,
        Container $container,
        ?Model $model
    ) {
        parent::__construct($container, $model);
        $this->machine = $machine;
        $this->adjustment = [];
        $this->event = $event;
    }

    final public function injectPrimary(
        SingleReflectionFormFactory $reflectionFormFactory,
        ReferencedPersonFactory $referencedPersonFactory,
        User $user
    ): void {
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->user = $user;
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        $holder = $this->machine->createHolder($this->model ?? null);
        $values = array_reduce(
            $this->adjustment,
            fn(array $prevValue, FormAdjustment $item): array => $item->adjust($prevValue, $holder),
            $values
        );
    }


    protected function setDefaults(): void
    {
        // TODO: Implement setDefaults() method.
    }

    /**
     * @param Form $form
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $teamContainer = $this->reflectionFormFactory->createContainer('fyziklani_team', ['name', 'password']);
        $form->addComponent($teamContainer, 'team');
        for ($member = 0; $member < 5; $member++) {
            $memberContainer = $this->referencedPersonFactory->createReferencedPerson(
                $this->getDef(),
                $this->event->getContestYear(),
                'email',
                $member !== 0,
                new SelfResolver($this->user),
                new SelfResolver($this->user),
                $this->event
            );
            $teamContainer->addComponent($memberContainer, 'member_' . $member);
        }
    }

    private function getDef()
    {
        return [
            'person' => [
                'other_name' => [
                    'required' => true,
                ],
                'family_name' => [
                    'required' => true,
                ],
            ],
            'person_info' => [
                'email' => [
                    'required' => true,
                ],
                'born' => [
                    'required' => false,
                    'description' => _('Pouze pro české a slovenské studenty.'),
                ],
            ],
            'person_history' => [
                'school_id' => [
                    'required' => true,
                    'description' => _(
                        'Napište prvních několik znaků vaší školy, školu pak vyberete ze seznamu. Pokud nelze školu nalézt, pošlete na email schola.novum@fykos.cz údaje o vaší škole jako název, adresu a pokud možno i odkaz na webovou stránku. Školu založíme a pošleme vám odpověď. Pak budete schopni dokončit registraci. Pokud nejste student, vyplňte "not a student".'
                    ),
                ],
                'study_year' => [
                    'required' => false,
                    'description' => _('Pro výpočet kategorie. Ponechte nevyplněné, pokud nejste ze SŠ/ZŠ.'),
                ],
            ],
            /*  'person_has_flag' => [
                  'spam_mff' => [
                      'required' => false,
                      'description' => _('Pouze pro české a slovenské studenty.'),
                  ],
              ],*/
        ];
    }
}
