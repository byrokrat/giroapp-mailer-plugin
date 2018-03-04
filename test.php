<?php
/**
 * A testscript to create fake messages
 */

declare(strict_types = 1);

namespace hanneskod\GiroappMailerPlugin;

require __DIR__ . '/src/GiroappMailerPlugin.php';

use byrokrat\giroapp\Event\DonorEvent;
use byrokrat\giroapp\Model\Donor;
use byrokrat\giroapp\Model\PostalAddress;
use byrokrat\giroapp\State\ActiveState;
use byrokrat\giroapp\Events;

$donor = new Donor(
    'mandateKey',
    new ActiveState,
    'mandateSource',
    'payerNumber',
    (new \byrokrat\banking\AccountFactory)->createAccount('50001111116'),
    new \byrokrat\id\PersonalId('820323-2775'),
    'donorName',
    new PostalAddress('', '', '', '', ''),
    'hannes.forsgard@fripost.org',
    'phone',
    new \byrokrat\amount\Currency\SEK('100'),
    'comment',
    new \DateTime,
    new \DateTime,
    []
);

(new MailingSubscriber)->onMailableEvent(
    new DonorEvent('testevent', $donor),
    Events::DONOR_ADDED,
    new \Symfony\Component\EventDispatcher\EventDispatcher
);
