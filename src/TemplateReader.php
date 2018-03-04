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

    public function getTemplatesByPostfix(string $postfix): iterable
    {
        foreach ($this->finder as $file) {
            if (preg_match("/\.$postfix$/", $file->getFilename())) {
                yield $file->getContents();
            }
        }
    }
}
