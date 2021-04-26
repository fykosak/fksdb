<?php
/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */

namespace NiftyGrid\Components;

use Nette;
use Nette\Application\UI\Component;
use Nette\Utils\Html;
use NiftyGrid;

class Action extends Component {

    public string $name;

    public string $label;

    /** @var callback|string */
    public $dialog;

    /** @var callback */
    public $callback;

    public bool $ajax = true;

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function setLabel(string $label): self {
        $this->label = $label;
        return $this;
    }

    /**
     * @param callback|string $dialog
     * @return Action
     */
    public function setConfirmationDialog($dialog): self {
        $this->dialog = $dialog;

        return $this;
    }

    public function setCallback(callable $callback): self {
        $this->callback = $callback;

        return $this;
    }

    public function getCallback(): callable {
        return $this->callback;
    }

    public function setAjax(bool $ajax): self {
        $this->ajax = $ajax;

        return $this;
    }

    /**
     * @return Html
     * @throws NiftyGrid\UnknownActionCallbackException
     */
    public function getAction(): Html {
        if (!isset($this->callback)) {
            throw new NiftyGrid\UnknownActionCallbackException("Action $this->name doesn't have callback.");
        }

        $option = Html::el('option')->setValue($this->name)->setText($this->label);

        if ($this->ajax) {
            $option->addClass('grid-ajax');
        }

        if (isset($this->dialog)) {
            $option->addData("grid-confirm", $this->dialog);
        }

        return $option;
    }
}
