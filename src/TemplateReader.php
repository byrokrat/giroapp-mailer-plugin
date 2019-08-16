<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use Symfony\Component\Finder\Finder;

final class TemplateReader
{
    /**
     * @var Finder
     */
    private $finder;

    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @return iterable & string[]
     */
    public function readTemplates(string $postfix): iterable
    {
        foreach ($this->finder as $file) {
            if (preg_match("/\.$postfix$/", $file->getFilename())) {
                yield $file->getContents();
            }
        }
    }
}
