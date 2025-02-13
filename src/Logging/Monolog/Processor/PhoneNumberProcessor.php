<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\NullablePhoneNumber;

class PhoneNumberProcessor implements ProcessorInterface
{
    #[\Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;
        foreach ($context as $key => $value) {
            if ($value instanceof NullablePhoneNumber) {
                $context[$key] = $value->toE164()?->jsonSerialize();
            }
        }

        return $context === $record->context ? $record : $record->with(context: $context);
    }
}
