<?php
/**
 * This file is part of giroapp-mailer-plugin.
 *
 * giroapp-mailer-plugin is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * giroapp-mailer-plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with giroapp-mailer-plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright 2018-21 Hannes Forsgård
 */

declare(strict_types = 1);

namespace byrokrat\giroapp\Mailer;

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
        $container['default_from_header'] = $env->readConfig('mailer_default_from_header');
        $container['default_reply_to_header'] = $env->readConfig('mailer_default_reply_to_header');
        $container['template_dir'] = $env->readConfig('mailer_template_dir');
        $container['queue_dir'] = $env->readConfig('mailer_queue_dir');

        $container[LoggerInterface::class] = $env->getLogger();

        $env->registerConsoleCommand($container[MailerClearConsole::class]);
        $env->registerConsoleCommand($container[MailerRemoveConsole::class]);
        $env->registerConsoleCommand($container[MailerSendConsole::class]);
        $env->registerConsoleCommand($container[MailerListConsole::class]);

        $env->registerListener($container[MailCreatingListener::class]);
        $env->registerListener($container[MailQueueingListener::class]);

        $env->registerStatistic($container[MailStatistic::class]);
    }
};

__HALT_COMPILER();
