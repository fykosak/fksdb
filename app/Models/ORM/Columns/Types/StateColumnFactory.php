<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Types;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\UI\NotSetBadge;
use Fykosak\NetteORM\Model\Model;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<TModel,ArgType>
 * @phpstan-template TModel of Model
 * @phpstan-template ArgType
 */
class StateColumnFactory extends ColumnFactory
{
    /** @phpstan-var array<string,array{badge:string,label:string}> */
    protected array $states = [];

    protected function createHtmlValue(Model $model): Html
    {
        $state = $model->{$this->modelAccessKey};
        if (is_null($state)) {
            return NotSetBadge::getHtml();
        }
        $stateDef = $this->getState($state);
        return Html::el('span')->addAttributes(['class' => $stateDef['badge']])->addText(_($stateDef['label']));
    }

    /**
     * @phpstan-param array<string,array{badge:string,label:string}> $states
     */
    public function setStates(array $states): void
    {
        $this->states = $states;
    }

    /**
     * @phpstan-return array{badge:string,label:string}
     */
    public function getState(string $state): array
    {
        if (isset($this->states[$state])) {
            return $this->states[$state];
        }
        return ['badge' => '', 'label' => ''];
    }

    protected function createFormControl(...$args): SelectBox
    {
        return new SelectBox($this->getTitle(), $this->getItems());
    }

    /**
     * @phpstan-return array<string,string>
     */
    protected function getItems(): array
    {
        $data = [];
        foreach ($this->states as $key => $state) {
            $data[$key] = $state['label'];
        }
        return $data;
    }
}
