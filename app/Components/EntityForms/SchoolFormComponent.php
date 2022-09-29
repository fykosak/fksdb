<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Factories\SchoolFactory;
use FKSDB\Components\Forms\Referenced\ReferencedId;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Form;

/**
 * @property SchoolModel|null $model
 */
class SchoolFormComponent extends EntityFormComponent
{

    public const CONT_ADDRESS = 'address';
    public const CONT_SCHOOL = 'school';

    private SchoolService $schoolService;
    private SchoolFactory $schoolFactory;

    final public function injectPrimary(
        SchoolFactory $schoolFactory,
        SchoolService $schoolService
    ): void {
        $this->schoolFactory = $schoolFactory;
        $this->schoolService = $schoolService;
    }

    protected function configureForm(Form $form): void
    {
        $form->addComponent($this->schoolFactory->createContainer(), self::CONT_SCHOOL);
    }

    /**
     * @throws ModelException
     */
    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        $schoolData = FormUtils::emptyStrToNull2($values[self::CONT_SCHOOL]);
        $this->schoolService->storeModel($schoolData, $this->model ?? null);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('School has been updated') : _('School has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void
    {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([self::CONT_SCHOOL => $this->model->toArray()]);
        } else {
            $this->getForm()->setDefaults([self::CONT_SCHOOL => ['address_id' => ReferencedId::VALUE_PROMISE]]);
        }
    }
}
