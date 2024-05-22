<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SchoolSelectField;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\SchoolLabelModel;
use FKSDB\Models\ORM\Services\SchoolLabelService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\LinkGenerator;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<SchoolLabelModel>
 */
final class SchoolLabelFormComponent extends EntityFormComponent
{
    private SchoolLabelService $schoolLabelService;
    private LinkGenerator $linkGenerator;

    public const CONTAINER = 'container';

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
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'school_label');
        $container->addField('school_label_key', ['required' => true]);
        $schoolContainer = new SchoolSelectField($this->container, $this->linkGenerator);
        $container->addComponent($schoolContainer, 'school_id');
        $form->addComponent($container, self::CONTAINER);
    }

    protected function handleFormSuccess(Form $form): void
    {
        /**
         * @phpstan-var array{container:array{
         *      school_label:string,
         *      school_id:int
         * }} $values
         */
        $values = $form->getValues('array');
        $data = FormUtils::emptyStrToNull2($values[self::CONTAINER]);
        $this->schoolLabelService->storeModel($data, $this->model);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('School label has been updated') : _('School label has been created'),
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
