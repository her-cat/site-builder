<?php


namespace HerCat\SiteBuilder;

use HerCat\SiteBuilder\Commands\BuildCommand;
use \Symfony\Component\Console\Application as ConsoleApplication;

/**
 * Class Application.
 *
 * @author her-cat <hxhsoft@foxmail.com>
 */
class Application extends ConsoleApplication
{
    /**
     * Application constructor.
     *
     * @param $name
     * @param $version
     */
    public function __construct($name, $version)
    {
        parent::__construct($name, $version);

        $this->add(new BuildCommand());
    }
}