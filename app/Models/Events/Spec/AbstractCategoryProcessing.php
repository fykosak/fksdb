<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Spec;

use FKSDB\Components\Forms\Factories\Events\OptionsProvider;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Nette\Utils\ArrayHash;

abstract class AbstractCategoryProcessing extends WithSchoolProcessing implements OptionsProvider
{

    protected PersonService $personService;

    public function __construct(PersonService $personService)
    {
        $this->personService = $personService;
    }

    protected function saveCategory(
        ?TeamCategory $category,
        ArrayHash $values,
        BaseHolder $holder,
        Logger $logger
    ): void {
        if (is_null($category)) {
            return;
        }
        $values['team']['category'] = $category->value;
        /** @var TeamModel2 $model */
        $model = $holder->getModel();
        $original = $model ? $model->category : null;
        if ($original != $category->value) {
            $logger->log(
                new Message(
                    sprintf(_('Team inserted to category %s.'), $category->label()),
                    Message::LVL_INFO
                )
            );
        }
    }

    final protected function innerProcess(
        ArrayHash $values,
        ModelHolder $holder,
        Logger $logger
    ): void {
        if (!isset($values['team'])) {
            return;
        }
        $result = $this->getCategory($holder, $values);
        $this->saveCategory($result, $values, $holder, $logger);
    }

    abstract protected function getCategory(BaseHolder $holder, ArrayHash $values): ?TeamCategory;

    protected function isBaseReallyEmpty(string $name): bool
    {
        $personIdControls = $this->getControl("$name.person_id");
        $personIdControl = reset($personIdControls);
        if ($personIdControl && $personIdControl->getValue(false)) {
            return false;
        }
        return parent::isBaseReallyEmpty($name);
    }

    private function getPersonHistory(BaseHolder $holder): ?PersonHistoryModel
    {
        $personControls = $this->getControl("$holder->name.person_id");
        $value = reset($personControls)->getValue(false);
        $person = $this->personService->findByPrimary($value);
        return $person->getHistoryByContestYear($holder->event->getContestYear());
    }

    public function getOptions(Field $field): array
    {
        $results = [];
        foreach (TeamCategory::casesForEvent($field->holder->event) as $category) {
            $results[$category->value] = $category->label();
        }
        return $results;
    }
}
