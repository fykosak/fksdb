<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<TeamModel2>
 */
final class NoteForm extends EntityFormComponent
{
    private ReflectionFactory $reflectionFormFactory;
    private TeamService2 $teamService;

    public function __construct(
        Container $container,
        TeamModel2 $model
    ) {
        parent::__construct($container, $model);
    }

    final public function injectPrimary(
        TeamService2 $teamService,
        ReflectionFactory $reflectionFormFactory
    ): void {
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->teamService = $teamService;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $teamContainer = $this->reflectionFormFactory->createContainerWithMetadata(
            'fyziklani_team',
            [
                'note' => ['required' => false],
                'internal_note' => ['required' => false],
            ],
            new FieldLevelPermission(FieldLevelPermission::ALLOW_FULL, FieldLevelPermission::ALLOW_FULL)
        );
        $form->addComponent($teamContainer, 'team');
    }

    protected function handleFormSuccess(Form $form): void
    {
        /** @phpstan-var array{team:array{note:string,internal_note:string}} $values */
        $values = $form->getValues('array');
        $this->teamService->storeModel($values['team'], $this->model);
        $this->getPresenter()->flashMessage(_('Notes has been updated'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('this');
    }

    protected function setDefaults(Form $form): void
    {
        $form->setDefaults(['team' => $this->model->toArray()]);
    }
}
