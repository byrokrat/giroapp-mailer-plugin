<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Plugin\PluginInterface;
use byrokrat\giroapp\Plugin\EnvironmentInterface;
use Pimple\Container;

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

        $env->registerSubscriber($container[MailingSubscriber::CLASS]);
    }
};

__HALT_COMPILER();
