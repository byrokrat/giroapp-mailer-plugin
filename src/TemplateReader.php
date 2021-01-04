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

use byrokrat\giroapp\Event\DonorEvent;
use byrokrat\giroapp\Event\DonorEmailUpdated;
use byrokrat\giroapp\Event\DonorStateUpdated;
use byrokrat\giroapp\Filesystem\FilesystemInterface;
use byrokrat\giroapp\Utils\ClassIdExtractor;
use hkod\frontmatter\Parser as FrontmatterParser;

final class TemplateReader
{
    private FilesystemInterface $filesystem;
    private FrontmatterParser $frontmatterParser;
    private string $defaultFrom;
    private string $defaultReplyTo;

    public function __construct(
        FilesystemInterface $filesystem,
        FrontmatterParser $frontmatterParser,
        string $defaultFrom,
        string $defaultReplyTo
    ) {
        $this->filesystem = $filesystem;
        $this->frontmatterParser = $frontmatterParser;
        $this->defaultFrom = $defaultFrom;
        $this->defaultReplyTo = $defaultReplyTo;
    }

    /**
     * @return \Generator<Template>
     */
    public function getTemplatesForEvent(DonorEvent $event): \Generator
    {
        $toAddress = $event instanceof DonorEmailUpdated
            ? $event->getNewEmail()
            : $event->getDonor()->getEmail();

        if (!$toAddress) {
            return;
        }

        $templateId = $event instanceof DonorStateUpdated
            ? $event->getNewState()->getStateId()
            : (string)new ClassIdExtractor($event);

        foreach ($this->getRawTemplatesForExtension($templateId) as $raw) {
            $parseResult = $this->frontmatterParser->parse($raw, $event);

            $metadata = array_change_key_case($parseResult->getFrontmatter(), CASE_LOWER);

            $body = trim($parseResult->getBody());

            if (empty($body)) {
                continue;
            }

            yield new Template(
                $body,
                $metadata['subject'] ?? '',
                $metadata['from'] ?? $this->defaultFrom,
                $metadata['reply-to'] ?? $metadata['replyto'] ?? $this->defaultReplyTo,
                $toAddress,
                (array)($metadata['cc'] ?? []),
                (array)($metadata['bcc'] ?? [])
            );
        }
    }

    /**
     * @return \Generator<string>
     */
    private function getRawTemplatesForExtension(string $extension): \Generator
    {
        foreach ($this->filesystem->readDir('') as $file) {
            if (preg_match("/\.$extension$/", $file->getFilename())) {
                yield $file->getContent();
            }
        }
    }
}
