<?php

namespace FKSDB\Components\Forms\Containers\Models;

/**
 * @note Code is copy+pasted from Nette\Forms\Controls\BaseControl.
 * @author Michal Koutný <michal@fykos.cz>
 */
trait OptionsTrait {

    /** @var array user options */

    private $options = [];

    /**
     * Sets user-specific option.
     * Options recognized by DefaultFormRenderer
     * - 'description' - textual or Html object description
     *
     * @param  string key
     * @param  mixed  value
     * @return self
     */
    public function setOption($key, $value) {
        if ($value === NULL) {
            unset($this->options[$key]);
        } else {
            $this->options[$key] = $value;
        }
        return $this;
    }

    /**
     * Returns user-specific option.
     * @param  string key
     * @param  mixed  default value
     * @return mixed
     */
    final public function getOption($key, $default = NULL) {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    /**
     * Returns user-specific options.
     * @return array
     */
    final public function getOptions() {
        return $this->options;
    }

}
