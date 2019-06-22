<?php

/*
 * This file is part of the her-cat/site-builder.
 *
 * (c) her-cat <hxhsoft@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace HerCat\SiteBuilder\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class BuildCommand.
 *
 * @author her-cat <hxhsoft@foxmail.com>
 */
class BuildCommand extends Command
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $nginxConfFile;

    /**
     * @var string
     */
    protected $nginxConfDirectory = '/usr/local/etc/nginx/conf.d/%s.conf';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('build')
            ->setDescription('Build site configure.');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fs = new Filesystem();
        $this->nginxConfFile = __DIR__.'/../stubs/default.conf';

        $helper = $this->getHelper('question');

        $config = [
            'DOMAIN' => '',
            'HOST' => '127.0.0.1',
            'PORT' => 80,
            'DIR' => '',
            'CREATE_DIR' => true,
        ];

        $question = new Question('domain name (example: <fg=yellow>site.com</fg=yellow>): ');
        $question->setValidator(function ($value) {
            if (empty(trim($value))) {
                throw new \Exception('The domain name can not be empty');
            }

            if (!preg_match('/(\w+\.){1,}\w+/', $value)) {
                throw new \Exception('The domain name is invalid, format: site.com');
            }

            return $value;
        });
        $question->setMaxAttempts(5);

        $config['DOMAIN'] = $helper->ask($input, $output, $question);

        $question = new Question('host [<fg=yellow>127.0.0.1</fg=yellow>]: ', '127.0.0.1');
        $question->setValidator(function ($value) {
            if (empty(trim($value))) {
                throw new \Exception('The host can not be empty');
            }

            $pattern = '/(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)\.(25[0-5]|2[0-4]\d|[0-1]\d{2}|[1-9]?\d)/';
            if (!preg_match($pattern, $value)) {
                throw new \Exception('The host is invalid, format: 127.0.0.1');
            }

            return $value;
        });

        $config['HOST'] = $helper->ask($input, $output, $question);

        $question = new Question('port [<fg=yellow>80</fg=yellow>]: ', 80);
        $question->setValidator(function ($value) {
            if (!is_numeric($value)) {
                throw new \Exception('The port must be a number');
            }

            return $value;
        });

        $config['PORT'] = $helper->ask($input, $output, $question);

        $question = new Question('site directory (example: <fg=yellow>/var/www/site</fg=yellow>): ');
        $question->setValidator(function ($value) {
            if (empty(trim($value))) {
                throw new \Exception('The site directory can not be empty');
            }

            if (!preg_match('/[a-z0-9\-_]+/', $value)) {
                throw new \Exception('The site directory is invalid, format: /var/www/site');
            }

            return $value;
        });

        $config['DIR'] = $helper->ask($input, $output, $question);
        if (!is_dir($config['DIR'])) {
            $question = new ConfirmationQuestion('The site directory does not exist, whether to create? [<fg=yellow>Y/n</fg=yellow>]: ', 'yes');
            $config['CREATE_DIR'] = $helper->ask($input, $output, $question);
        }

        $this->appendHostConfiguration($config);
        $this->createNginxConfiguration($config);
        $this->createSiteDir($config);
    }

    public function appendHostConfiguration($config)
    {
        $this->fs->appendToFile(
            '/etc/hosts',
            "{$config['HOST']} {$config['DOMAIN']}".PHP_EOL
        );
    }

    public function createNginxConfiguration($config)
    {
        $content = str_replace(array_keys($config), array_values($config), file_get_contents($this->nginxConfFile));

        $fileName = sprintf($this->nginxConfDirectory, $config['DOMAIN']);
        $this->fs->dumpFile($fileName, $content);
    }

    public function createSiteDir($config)
    {
        if ($config['CREATE_DIR']) {
            $this->fs->mkdir($config['DIR']);
        }
    }
}
