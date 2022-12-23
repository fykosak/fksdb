<?php

declare(strict_types=1);

namespace NiftyGrid\Components;

use Nette\Application\UI\Component;
use Nette\Utils\Html;

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */
class Button extends Component
{
    private string $label;

    /** @var callback */
    private $link;
    private string $class;
    /** @var callback */
    private $dialog;
    /** @var callback */
    private $show;

    public function __construct(string $label, callable $link)
    {
        $this->label = $label;
        $this->link = $link;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function setConfirmationDialog(callable $message): self
    {
        $this->dialog = $message;
        return $this;
    }

    /**
     * @param mixed $row
     */
    public function getConfirmationDialog($row): string
    {
        return ($this->dialog)($row);
    }

    public function setShow(callable $show): self
    {
        $this->show = $show;
        return $this;
    }

    /**
     * @param mixed $row
     */
    public function toHtml($row): Html
    {
        if (isset($this->show) && !($this->show)($row)) {
            return Html::el('span');
        }
        $el = Html::el('a')
            ->href(($this->link)($row))
            ->addText($this->label)
            ->addAttributes([
                'class' => 'grid-button ' . ($this->class ?? 'btn btn-secondary'),
                'title' => $this->label,
            ]);
        if (isset($this->dialog)) {
            $el->addClass('grid-confirm')
                ->addData('grid-confirm', $this->getConfirmationDialog($row));
        }
        return $el;
    }
}
