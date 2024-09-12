<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Forms;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<ScheduleItemModel,array{container:array{
 *                name_cs:string,
 *                name_en:string,
 *                description_cs:string|null,
 *                description_en:string|null,
 *                capacity:int|null,
 *                price_czk:int|null,
 *                price_eur:int|null,
 *  }}>
 */
class ScheduleItemForm extends ModelForm
{
    public const CONTAINER = 'container';

    private ScheduleItemService $scheduleItemService;
    private ScheduleGroupModel $group;

    public function __construct(
        ScheduleGroupModel $group,
        Container $container,
        ?ScheduleItemModel $model
    ) {
        parent::__construct($container, $model);
        $this->group = $group;
    }

    final public function injectPrimary(ScheduleItemService $scheduleItemService): void
    {
        $this->scheduleItemService = $scheduleItemService;
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([
                self::CONTAINER => $this->model->toArray(),
            ]);
        } else {
            $form->setDefaults([
                self::CONTAINER => [
                    'schedule_group_id' => $this->group->getPrimary(),
                ],
            ]);
        }
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'schedule_item');
        $container->addField('name_cs', ['required' => true]);
        $container->addField('name_en', ['required' => true]);
        $container->addField('description_cs', ['required' => false]);
        $container->addField('description_en', ['required' => false]);
        $container->addField('long_description_cs', ['required' => false]);
        $container->addField('long_description_en', ['required' => false]);
        $container->addField('payable', ['required' => false]);
        $container->addField('available', ['required' => false]);
        $container->addField('begin', ['required' => false]);
        $container->addField('end', ['required' => false]);
        $container->addField('capacity', ['required' => false]);
        $container->addField('price_czk', ['required' => false]);
        $container->addField('price_eur', ['required' => false]);
        $items = [];
        /** @var ScheduleGroupModel $group */
        foreach ($this->group->event->getScheduleGroups() as $group) {
            $items[$group->schedule_group_id] = $this->translator->getVariant($group->name)
                . '(' . $group->schedule_group_type->value . ')';
        }
        $container->addSelect('schedule_group_id', _('Group'), $items);
        $form->addComponent($container, self::CONTAINER);
    }

    protected function innerSuccess(array $values, Form $form): ScheduleItemModel
    {
        /** @var ScheduleItemModel $model */
        $model = $this->scheduleItemService->storeModel($values[self::CONTAINER], $this->model);
        return $model;
    }

    protected function successRedirect(Model $model): void
    {
        $this->flashMessage(sprintf(_('Item "#%d" has been saved.'), $model->schedule_item_id), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect(':Schedule:Group:detail', ['id' => $model->schedule_group_id]);
    }
}
