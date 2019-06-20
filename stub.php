<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Plugin\PluginInterface;
use byrokrat\giroapp\Plugin\EnvironmentInterface;
use byrokrat\giroapp\Plugin\ApiVersionConstraint;
use Psr\Log\LoggerInterface;
use Pimple\Container;

require 'phar://' . __FILE__ . '/vendor/autoload.php';

return new class implements PluginInterface {
    public function loadPlugin(EnvironmentInterface $env): void
    {
        $env->assertApiVersion(new ApiVersionConstraint('giroapp-mailer-plugin', '1.*'));

        $container = new Container;

        $container->register(new DependenciesProvider);

        $container['smtp_string'] = $env->readConfig('mailer_smtp_string');
        $container['template_dir'] = $env->readConfig('mailer_template_dir');
        $container['queue_dir'] = $env->readConfig('mailer_queue_dir');
        $container['queue_dir'] = $env->readConfig('mailer_queue_dir');
        $container[LoggerInterface::CLASS] = $env->getLogger();

        $env->registerConsoleCommand($container[MailerSendConsole::CLASS]);
        $env->registerConsoleCommand($container[MailerStatusConsole::CLASS]);

        $env->registerListener($container[DonorStateListener::CLASS]);
    }
};

__HALT_COMPILER();
