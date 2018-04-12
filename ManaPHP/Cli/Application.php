<?php

namespace ManaPHP\Cli;

/**
 * Class ManaPHP\Cli\Application
 *
 * @package application
 *
 * @property \ManaPHP\Cli\HandlerInterface $cliHandler
 */
class Application extends \ManaPHP\Application
{
    /** @noinspection MagicMethodsValidityInspection */
    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * Application constructor.
     *
     * @param \ManaPHP\Loader      $loader
     * @param \ManaPHP\DiInterface $di
     */
    public function __construct($loader, $di = null)
    {
        parent::__construct($loader, $di);

        if ($appDir = $this->alias->get('@app')) {
            if (is_dir("$appDir/Cli")) {
                $this->alias->set('@cli', "$appDir/Cli/Controllers");
                if ($appNamespace = $this->alias->get('@ns.app')) {
                    $this->alias->set('@ns.cli', "$appNamespace/Cli/Controllers");
                }
            } elseif (($calledClass = get_called_class()) !== __CLASS__) {
                $this->alias->set('@cli', "$appDir/Controllers");
                $this->alias->set('@ns.cli', substr($calledClass, 0, strrpos($calledClass, '\\')));
            }
        }
    }

    public function registerServices()
    {
        $this->configure->bootstraps = array_diff($this->configure->bootstraps, ['debugger']);

        parent::registerServices();

        $this->_di->setShared('cliHandler', 'ManaPHP\Cli\Handler');
        $this->_di->setShared('console', 'ManaPHP\Cli\Console');
        $this->_di->setShared('arguments', 'ManaPHP\Cli\Arguments');
        $this->_di->setShared('commandInvoker', 'ManaPHP\Cli\Command\Invoker');
    }

    /**
     * @throws \ManaPHP\Configuration\Configure\Exception
     */
    public function main()
    {
        if ($this->_configFile) {
            $this->configure->loadFile($this->_configFile);
        }

        $this->registerServices();

        exit($this->cliHandler->handle());
    }
}