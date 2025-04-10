<?php declare(strict_types=1);

/*
 * This file is part of chrisguitarguy/request-id-bundle

 * Copyright (c) Christopher Davis <http://christopherdavis.me>
 *
 * For full copyright information see the LICENSE file distributed
 * with this source code.
 *
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace RequestIdBundle\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use RequestIdBundle\Service\RequestIdStorage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Adds the request ID to the Monolog record's `extra` key so it can be used
 * in formatters, etc.
 *
 * @since   1.0
 */
#[AutoconfigureTag('monolog.processor')]
final class RequestIdProcessor implements ProcessorInterface
{
    public function __construct(private readonly RequestIdStorage $storage)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if ($id = $this->storage->getRequestId()) {
            $record->extra['request_id'] = $id;
        }

        return $record;
    }
}
