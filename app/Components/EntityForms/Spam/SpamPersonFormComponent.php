<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Spam;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Columns\Tables\PersonHistory\StudyYearNewColumnFactory;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\ORM\Services\SchoolLabelService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<PersonHistoryModel,array{container:array{
 *       other_name:string,
 *       family_name:string,
 *       person_history_container:array{
 *           school_label_key:string,
 *           study_year_new:string
 *       }
 *  }}>
 */
final class SpamPersonFormComponent extends ModelForm
{
    private SchoolLabelService $schoolLabelService;
    private ContestYearModel $contestYear;

    private ReflectionFactory $reflectionFactory;
    private Handler $handler;

    public const CONTAINER = 'container';
    public const PERSON_HISTORY_CONTAINER = 'person_history_container';

    public function __construct(ContestYearModel $contestYear, Container $container, ?PersonHistoryModel $model)
    {
        parent::__construct($container, $model);
        $this->contestYear = $contestYear;
        $this->handler = new Handler($contestYear, $this->container);
    }

    public function injectService(
        SchoolLabelService $schoolLabelService,
        ReflectionFactory $reflectionFactory
    ): void {
        $this->schoolLabelService = $schoolLabelService;
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'person');
        $container->addField('other_name', ['required' => true]);
        $container->addField('family_name', ['required' => true]);

        $personHistoryContainer = new ModelContainer($this->container, 'person_history');
        $personHistoryContainer->addField('school_label_key', ['required' => true]);
        $studyYearControl = $this->reflectionFactory->createField(
            'person_history',
            'study_year_new',
            $this->contestYear,
            StudyYearNewColumnFactory::FLAG_HS
        );
        $personHistoryContainer->addComponent($studyYearControl, 'study_year_new');
        $personHistoryContainer->addText('ac_year', _('Academic year'))->setDisabled();

        $container->addComponent($personHistoryContainer, self::PERSON_HISTORY_CONTAINER);


        $form->addComponent($container, self::CONTAINER);
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([
                self::CONTAINER => array_merge(
                    $this->model->person->toArray(),
                    [self::PERSON_HISTORY_CONTAINER => $this->model->toArray()]
                )
            ]);
        }
    }

    /**
     * @throws MissingSchoolLabelException
     */
    protected function innerSuccess(array $values, Form $form): Model
    {
        $data = $values[self::CONTAINER];

        $transformedData = [
            'other_name' => $data['other_name'],
            'family_name' => $data['family_name'],
            'school_label_key' => $data[self::PERSON_HISTORY_CONTAINER]['school_label_key'],
            'study_year_new' => $data[self::PERSON_HISTORY_CONTAINER]['study_year_new'],
        ];

        if (!$this->schoolLabelService->exists($transformedData['school_label_key'])) {
            throw new MissingSchoolLabelException();
        }
        return $this->handler->storePerson($transformedData, $this->model);
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Person has been updated') : _('Person has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }
}
