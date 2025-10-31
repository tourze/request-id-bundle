<?php

declare(strict_types=1);

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
 * 将 request ID 添加到 Monolog record 的 `extra` 键中，以便在 formatters 等地方使用
 *
 * @since   1.0
 */
#[AutoconfigureTag(name: 'monolog.processor')]
final class RequestIdProcessor implements ProcessorInterface
{
    public function __construct(private readonly RequestIdStorage $storage)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if (($id = $this->storage->getRequestId()) !== null && '' !== $id) {
            $record->extra['request_id'] = $id;
        }

        return $record;
    }
}
