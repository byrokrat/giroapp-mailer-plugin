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
 * Copyright 2018-20 Hannes Forsgård
 */

declare(strict_types = 1);

namespace byrokrat\giroapp\Mailer;

use byrokrat\giroapp\Event\DonorEvent;
use byrokrat\giroapp\Event\DonorEmailUpdated;
use byrokrat\giroapp\Event\DonorStateUpdated;
use byrokrat\giroapp\Utils\ClassIdExtractor;
use hkod\frontmatter\Parser as FrontmatterParser;
use Symfony\Component\Finder\Finder;

final class TemplateReader
{
    private Finder $finder;
    private FrontmatterParser $frontmatterParser;

    // TODO använd filesystem istället...
    public function __construct(Finder $finder, FrontmatterParser $frontmatterParser)
    {
        $this->frontmatterParser = $frontmatterParser;
        $this->finder = $finder;
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
                $metadata['from'] ?? '',
                $metadata['reply-to'] ?? $metadata['replyto'] ?? '',
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
        foreach ($this->finder as $file) {
            if (preg_match("/\.$extension$/", $file->getFilename())) {
                yield $file->getContents();
            }
        }
    }
}
