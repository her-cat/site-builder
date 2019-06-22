<?php

/*
 * This file is part of the her-cat/site-builder.
 *
 * (c) her-cat <hxhsoft@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace HerCat\SiteBuilder;

use HerCat\SiteBuilder\Commands\BuildCommand;
use Symfony\Component\Console\Application as ConsoleApplication;

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
