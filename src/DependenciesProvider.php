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

declare(strict_types=1);

namespace byrokrat\giroapp\Mailer;

final class DependenciesProvider implements \Pimple\ServiceProviderInterface
{
    public function register(\Pimple\Container $container): void
    {
        $container[MailerClearConsole::class] = function ($c) {
            return new MailerClearConsole($c[MessageRepositoryInterface::class], $c[\Psr\Log\LoggerInterface::class]);
        };

        $container[MailerListConsole::class] = function ($c) {
            return new MailerListConsole($c[MessageRepositoryInterface::class]);
        };

        $container[MailerRemoveConsole::class] = function ($c) {
            return new MailerRemoveConsole($c[MessageRepositoryInterface::class], $c[\Psr\Log\LoggerInterface::class]);
        };

        $container[MailerSendConsole::class] = function ($c) {
            return new MailerSendConsole(
                $c[MessageRepositoryInterface::class],
                $c[TransportInterface::class],
                $c[\Psr\Log\LoggerInterface::class]
            );
        };

        $container[MailStatistic::class] = function ($c) {
            return new MailStatistic($c[MessageRepositoryInterface::class]);
        };

        $container[MailCreatingListener::class] = function ($c) {
            return new MailCreatingListener(
                $c[MessageFactoryInterface::class],
                $c[MessageBuffer::class],
                $c[TemplateReader::class],
                $c[\Psr\Log\LoggerInterface::class]
            );
        };

        $container[MailQueueingListener::class] = function ($c) {
            return new MailQueueingListener(
                $c[MessageBuffer::class],
                $c[MessageRepositoryInterface::class],
                $c[\Psr\Log\LoggerInterface::class]
            );
        };

        $container[MessageBuffer::class] = function ($c) {
            return new MessageBuffer();
        };

        $container[TemplateReader::class] = function ($c) {
            return new TemplateReader(
                $c[\byrokrat\giroapp\Filesystem\FilesystemInterface::class],
                $c[\hkod\frontmatter\Parser::class],
                $c['default_from_header'],
                $c['default_reply_to_header']
            );
        };

        $container[\byrokrat\giroapp\Filesystem\FilesystemInterface::class] = function ($c) {
            return new \byrokrat\giroapp\Filesystem\StdFilesystem(
                $c['template_dir'],
                new \Symfony\Component\Filesystem\Filesystem()
            );
        };

        $container[\hkod\frontmatter\Parser::class] = function ($c) {
            return (new \hkod\frontmatter\ParserBuilder())
                ->addFrontmatterPass(new \hkod\frontmatter\MustacheParser())
                ->addFrontmatterPass(new \hkod\frontmatter\YamlParser())
                ->addBodyPass(new \hkod\frontmatter\MustacheParser())
                ->buildParser();
        };

        $container[MessageFactoryInterface::class] = function ($c) {
            return new GenkgoMessageFactory(new \Genkgo\Mail\FormattedMessageFactory());
        };

        $container[MessageRepositoryInterface::class] = function ($c) {
            return new GenkgoRepository($c[\Genkgo\Mail\Queue\QueueInterface::class]);
        };

        $container[\Genkgo\Mail\Queue\QueueInterface::class] = function ($c) {
            return new \Genkgo\Mail\Queue\FilesystemQueue($c['queue_dir']);
        };

        $container[TransportInterface::class] = function ($c) {
            return new GenkgoTransport($c[\Genkgo\Mail\TransportInterface::class]);
        };

        $container[\Genkgo\Mail\TransportInterface::class] = function ($c) {
            return new \Genkgo\Mail\Transport\SmtpTransport(
                \Genkgo\Mail\Protocol\Smtp\ClientFactory::fromString($c['smtp_string'])->newClient(),
                \Genkgo\Mail\Transport\EnvelopeFactory::useExtractedHeader()
            );
        };
    }
}
