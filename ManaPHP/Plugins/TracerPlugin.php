<?php
namespace ManaPHP\Plugins;

use ManaPHP\Plugin;

class TracerPlugin extends Plugin
{
    /**
     * @var int
     */
    protected $_params = 3;

    /**
     * @var int
     */
    protected $_return = 0;

    /**
     * @var int
     */
    protected $_max_depth = 2;

    /**
     * @var int
     */
    protected $_mem_delta = 1;

    /**
     * TracerPlugin constructor.
     *
     * @param array $options
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            if (isset($options['params'])) {
                $this->_params = $options['params'];
            }

            if (isset($options['return'])) {
                $this->_return = $options['return'];
            }

            if (isset($options['max_depth'])) {
                $this->_max_depth = $options['max_depth'];
            }

            if (isset($options['mem_delta'])) {
                $this->_mem_delta = $options['mem_delta'];
            }
        }
    }

    public function init()
    {
        if (function_exists('xdebug_start_trace')) {
            ini_set('xdebug.collect_return', $this->_return);
            ini_set('xdebug.collect_params', $this->_params);
            ini_set('xdebug.var_display_max_depth', $this->_max_depth);
            ini_set('xdebug.show_mem_delta', $this->_mem_delta);

            $this->attachEvent('app:beginRequest', [$this, 'onBeginRequest']);
            $this->attachEvent('app:endRequest', [$this, 'onEndRequest']);
        }
    }

    public function onBeginRequest()
    {
        $file = $this->alias->resolve('@data/tracer/trace_{ymd_His}_{8}.log');
        $dir = dirname($file);
        if (!is_dir($dir)) {
            /** @noinspection MkdirRaceConditionInspection */
            @mkdir($dir, 0777, true);
        }

        /** @noinspection ForgottenDebugOutputInspection */
        xdebug_start_trace($file);
    }

    public function onEndRequest()
    {
        /** @noinspection ForgottenDebugOutputInspection */
        @xdebug_stop_trace();
    }
}