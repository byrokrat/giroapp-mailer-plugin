<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Plugin\PluginInterface;
use byrokrat\giroapp\Plugin\EnvironmentInterface;
use Pimple\Container;

require 'phar://' . __FILE__ . '/vendor/autoload.php';

return new class implements PluginInterface {
    public function loadPlugin(EnvironmentInterface $env): void
    {
        $container = new Container;

        $container->register(new DependenciesProvider);

        $container['smtp_string'] = $env->readConfig('mailer_smtp_string');
        $container['template_dir'] = $env->readConfig('mailer_template_dir');
        $container['queue_dir'] = $env->readConfig('mailer_queue_dir');

        $env->registerCommand($container[MailerSendCommand::CLASS]);
        $env->registerCommand($container[MailerStatusCommand::CLASS]);

        $listener = $container[DonorStateListener::CLASS];
        $listener->setEventDispatcher($env->getEventDispatcher());

        $env->registerListener($listener);
    }
};

__HALT_COMPILER();
