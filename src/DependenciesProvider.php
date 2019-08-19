<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\Protocol\Smtp\ClientFactory;
use Genkgo\Mail\TransportInterface;
use Genkgo\Mail\Transport\SmtpTransport;
use Genkgo\Mail\Transport\EnvelopeFactory;
use Genkgo\Mail\Queue\QueueInterface;
use Genkgo\Mail\Queue\FilesystemQueue;
use Symfony\Component\Finder\Finder;
use hkod\frontmatter\Parser as FrontmatterParser;
use hkod\frontmatter\ParserBuilder;
use hkod\frontmatter\MustacheParser;
use hkod\frontmatter\YamlParser;
use Psr\Log\LoggerInterface;

final class DependenciesProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container[MailerClearConsole::CLASS] = function ($c) {
            return new MailerClearConsole($c[LoggerInterface::CLASS], $c[QueueInterface::CLASS]);
        };

        $container[MailerRemoveConsole::CLASS] = function ($c) {
            return new MailerRemoveConsole($c[LoggerInterface::CLASS], $c[QueueInterface::CLASS]);
        };

        $container[MailerSendConsole::CLASS] = function ($c) {
            return new MailerSendConsole(
                $c[LoggerInterface::CLASS],
                $c[QueueInterface::CLASS],
                $c[TransportInterface::CLASS]
            );
        };

        $container[MailerStatusConsole::CLASS] = function ($c) {
            return new MailerStatusConsole($c[LoggerInterface::CLASS], $c[QueueInterface::CLASS]);
        };

        $container[DonorEventListener::CLASS] = function ($c) {
            return new DonorEventListener(
                $c[TemplateReader::CLASS],
                $c[MessageFactory::CLASS],
                $c[QueueInterface::CLASS],
                $c[LoggerInterface::CLASS]
            );
        };

        $container[TemplateReader::CLASS] = function ($c) {
            return new TemplateReader((new Finder)->files()->in($c['template_dir']));
        };

        $container[MessageFactory::CLASS] = function ($c) {
            return new MessageFactory(
                $c[FrontmatterParser::CLASS],
                new FormattedMessageFactory
            );
        };

        $container[FrontmatterParser::CLASS] = function ($c) {
            return (new ParserBuilder)->addFrontmatterPass(new MustacheParser)
                ->addFrontmatterPass(new YamlParser)
                ->addBodyPass(new MustacheParser)
                ->buildParser();
        };

        $container[QueueInterface::CLASS] = function ($c) {
            return new FilesystemQueue($c['queue_dir']);
        };

        $container[TransportInterface::CLASS] = function ($c) {
            return new SmtpTransport(
                ClientFactory::fromString($c['smtp_string'])->newClient(),
                EnvelopeFactory::useExtractedHeader()
            );
        };
    }
}
