<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailer;

class DependencyLocator
{
    /**
     * @var array
     */
    private static $settings = [];

    public static function setup(array $settings): void
    {
        self::$settings = $settings;

        foreach (['template_dir', 'queue_dir'] as $dirname) {
            if (!is_dir($settings[$dirname])) {
                mkdir($settings[$dirname]);
            }
        }
    }

    public static function getFrontmatterParser(): \hkod\frontmatter\Parser
    {
        return (new \hkod\frontmatter\ParserBuilder)
            ->addFrontmatterPass(new \hkod\frontmatter\MustacheParser)
            ->addFrontmatterPass(new \hkod\frontmatter\YamlParser)
            ->addBodyPass(new \hkod\frontmatter\MustacheParser)
            ->buildParser();
    }

    public static function getTemplateReader(): TemplateReader
    {
        return new TemplateReader(
            (new \Symfony\Component\Finder\Finder)->files()->in(self::$settings['template_dir'])
        );
    }

    public static function getMessageFactory(): MessageFactory
    {
        return new MessageFactory(
            self::getFrontmatterParser(),
            new \Genkgo\Mail\FormattedMessageFactory
        );
    }

    public static function getMessageTransport(): \Genkgo\Mail\TransportInterface
    {
        // return new \Genkgo\Mail\Transport\NullTransport;

        return new \Genkgo\Mail\Transport\SmtpTransport(
            \Genkgo\Mail\Protocol\Smtp\ClientFactory::fromString(self::$settings['smtp_string'])->newClient(),
            \Genkgo\Mail\Transport\EnvelopeFactory::useExtractedHeader()
        );
    }

    public static function getMessageQueue(): \Genkgo\Mail\Queue\QueueInterface
    {
        return new \Genkgo\Mail\Queue\FilesystemQueue(self::$settings['queue_dir']);
    }

    public static function getWorker(): Worker
    {
        return new Worker(
            self::getTemplateReader(),
            self::getMessageFactory(),
            self::getMessageQueue()
        );
    }
}
