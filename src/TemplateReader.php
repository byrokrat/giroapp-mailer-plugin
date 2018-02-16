<?php

declare(strict_types = 1);

namespace hanneskod\GiroappMailerPlugin;

use Symfony\Component\Finder\Finder;

class TemplateReader
{
    /**
     * @var Finder
     */
    private $finder;

    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

    public function getTemplatesForEvent(string $eventName): iterable
    {
        foreach ($this->finder as $file) {
            if (preg_match("/\.$eventName/", $file->getFilename())) {
                yield $file->getContents();
            }
        }
    }
}
