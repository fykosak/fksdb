<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Spam;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SchoolSelectField;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\SchoolLabelModel;
use FKSDB\Models\ORM\Services\SchoolLabelService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Forms\Form;
use ValueError;

/**
 * @phpstan-extends ModelForm<SchoolLabelModel,array{container:array{
 *       school_label_key:string,
 *       school_id:int
 *  }}>
 */
final class SchoolLabelFormComponent extends ModelForm
{
    private SchoolLabelService $schoolLabelService;
    private LinkGenerator $linkGenerator;

    private ContestYearModel $contestYear;

    public const CONTAINER = 'container';

    public function __construct(Container $container, ?Model $model, ContestYearModel $contestYear)
    {
        parent::__construct($container, $model);
        $this->contestYear = $contestYear;
    }

    public function injectService(
        SchoolLabelService $schoolLabelService,
        LinkGenerator $linkGenerator
    ): void {
        $this->schoolLabelService = $schoolLabelService;
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     * @throws InvalidLinkException
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'school_label');
        $container->addField('school_label_key', ['required' => true]);
        $schoolContainer = new SchoolSelectField($this->container, $this->linkGenerator);
        $container->addComponent($schoolContainer, 'school_id');
        $form->addComponent($container, self::CONTAINER);
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }

    protected function innerSuccess(array $values, Form $form): Model
    {
        $data = $values[self::CONTAINER];

        // prevent adding already existing label
        if (!isset($this->model) && $this->schoolLabelService->exists($data['school_label_key'])) {
            throw new ValueError('School label already exists.');
        }

        if (!$data['school_id']) {
            throw new ValueError('School Id cannot be null.');
        }

        $handler = new Handler($this->contestYear, $this->container);
        return $handler->storeSchool($data['school_label_key'], intval($data['school_id']), $this->model);
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('School label has been updated.') : _('School label has been created.'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }
}
