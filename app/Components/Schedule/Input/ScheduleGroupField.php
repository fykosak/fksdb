<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponentTrait;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\SelectBox;

class ScheduleGroupField extends SelectBox
{
    use FrontEndComponentTrait;

    public ScheduleGroupModel $group;

    /**
     * @throws BadRequestException
     * @phpstan-param GettextTranslator<'cs'|'en'> $translator
     */
    public function __construct(ScheduleGroupModel $group, GettextTranslator $translator)
    {
        if ($group->registration_end) {
            parent::__construct(
                sprintf(
                    _('%s -- end of registration: %s'),
                    $translator->getVariant($group->name),
                    $group->registration_end->format(_('__date_time'))
                )
            );
        } else {
            parent::__construct($translator->getVariant($group->name));
        }

        $this->group = $group;
        $this->registerFrontend('schedule.group-container');
        $this->appendProperty();
        $items = [];
        $disabled = [];
        /** @var ScheduleItemModel $item */
        foreach ($this->group->getItems() as $item) {
            $items[$item->getPrimary()] = sprintf(
                _('%s - %s'),
                $translator->getVariant($item->name),
                $translator->getVariant($item->description)
            );
            if (!$item->available) {
                $disabled[] = $item->getPrimary();
            }
        }
        $this->setItems($items)->setPrompt(_('Not selected'))->setDisabled($disabled);
    }

    public function setModel(PersonScheduleModel $model): self
    {
        /** @phpstan-ignore-next-line */
        if (is_array($this->disabled) && isset($this->disabled[$model->schedule_item_id])) {
            unset($this->disabled[$model->schedule_item_id]);
        }
        return parent::setValue($model->schedule_item_id);
    }

    /**
     * @throws \Exception
     * @phpstan-return array{
     *     group:array<string,mixed>,
     * }
     */
    protected function getData(): array
    {
        $group = $this->group->__toArray();
        $itemList = [];
        /** @var ScheduleItemModel $item */
        foreach ($this->group->getItems() as $item) {
            $itemList[] = $item->__toArray();
        }

        $group['items'] = $itemList;
        return ['group' => $group,];
    }
}
