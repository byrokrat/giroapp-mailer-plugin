<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailer;

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

    public function readTemplates(string $postfix): iterable
    {
        foreach ($this->finder as $file) {
            if (preg_match("/\.$postfix$/", $file->getFilename())) {
                yield $file->getContents();
            }
        }
    }
}
