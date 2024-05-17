<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Spam;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SchoolSelectField;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Columns\Tables\PersonHistory\StudyYearNewColumnFactory;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\Spam\SpamPersonModel;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\ORM\Services\Spam\SpamPersonService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<SpamPersonModel>
 */
final class PersonFormComponent extends EntityFormComponent
{
    private ContestYearModel $contestYear;

    private SpamPersonService $spamPersonService;
    private ReflectionFactory $reflectionFactory;

    public const CONTAINER = 'container';

    public function __construct(ContestYearModel $contestYear, Container $container, ?SpamPersonModel $model)
    {
        parent::__construct($container, $model);
        $this->contestYear = $contestYear;
    }

    public function injectService(
        SpamPersonService $spamPersonService,
        ReflectionFactory $reflectionFactory
    ): void {
        $this->spamPersonService = $spamPersonService;
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'spam_person');
        $container->addField('other_name', ['required' => true]);
        $container->addField('family_name', ['required' => true]);
        $container->addField('spam_school_label', ['required' => true]);
        $studyYearControl = $this->reflectionFactory->createField(
            'spam_person',
            'study_year_new',
            $this->contestYear,
            StudyYearNewColumnFactory::FLAG_HS
        );
        $container->addComponent($studyYearControl, 'study_year_new');
        $container->addField('ac_year', ['required' => true]);
        $container->addField('note', ['required' => false]);
        $form->addComponent($container, self::CONTAINER);
    }

    protected function handleFormSuccess(Form $form): void
    {
        /**
         * @phpstan-var array{container:array{
         *      other_name:string,
         *      family_name:string,
         *      spam_school_label:string,
         * }} $values
         */
        $values = $form->getValues('array');
        $data = FormUtils::emptyStrToNull2($values[self::CONTAINER]);
        $this->spamPersonService->storeModel($data, $this->model);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Person has been updated.') : _('Person has been created.'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }
}
