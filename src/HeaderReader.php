<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use Genkgo\Mail\MessageInterface;

final class HeaderReader
{
    /**
     * @var MessageInterface
     */
    private $message;

    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }

    public function readHeader(string $name): string
    {
        $value = '';

        foreach ($this->message->getHeader($name) as $header) {
            $value = (string)$header->getValue();
            break;
        }

        return (string)iconv_mime_decode($value);
    }
}
