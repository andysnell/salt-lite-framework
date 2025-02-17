<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\LogglyFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\NoopHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Exception\InvalidHandlerConfiguration;
use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileMode;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use Psr\Log\LoggerInterface;

use function PhoneBurner\SaltLite\Framework\ghost;

class LoggerServiceFactory implements ServiceFactory
{
    public const array DEFAULT_FORMATTERS = [
        LogglyHandler::class => LogglyFormatter::class,
        RotatingFileHandler::class => LineFormatter::class,
        StreamHandler::class => LineFormatter::class,
        SlackWebhookHandler::class => LineFormatter::class,
        ErrorLogHandler::class => LineFormatter::class,
        TestHandler::class => LineFormatter::class,
    ];

    public function __invoke(App $app, string $id): LoggerInterface
    {
        return new PsrLoggerAdapter(ghost(function (Logger $logger) use ($app): void {
            $logger->__construct(
                $app->config->get('logging.channel') ?? Str::kabob($app->config->get('app.name')),
                \array_values(\array_map(function (array|LoggingHandlerConfigStruct $params): HandlerInterface {
                    if ($params instanceof LoggingHandlerConfigStruct) {
                        return $this->handler(
                            $params->handler_class,
                            $params->handler_options ?? [],
                            $params->formatter_class,
                            $params->formatter_options ?? [],
                        );
                    }

                    return $this->handler(
                        $params['handler_class'],
                        $params['handler_options'] ?? [],
                        $params['formatter_class'] ?? null,
                        $params['formatter_options'] ?? [],
                    );
                }, $app->config->get('logging.handlers') ?? [])),
                \array_map($app->services->get(...), $app->config->get('logging.processors') ?? []),
            );

            // On resolution, replace the resolved logger as the container's
            // logger instance, which should also consume any buffered log
            // entries from the default buffer logger.
            $app->services->setLogger($logger);
        }));
    }

    private function handler(
        string $handler_class,
        array $handler_options,
        string|null $formatter_class = null,
        array $formatter_options = [],
    ): HandlerInterface {

        // Standardize the level and bubble options
        $handler_options['level'] = LogLevel::instance($handler_options['level'] ?? LogLevel::Info)->toMonlogLogLevel();
        $handler_options['bubble'] = (bool)($handler_options['bubble'] ?? true);

        $handler = match ($handler_class) {
            LogglyHandler::class => new LogglyHandler(
                $handler_options['token'] ?? throw new InvalidHandlerConfiguration('Missing Loggly API Token'),
                $handler_options['level'],
                $handler_options['bubble'],
            ),
            RotatingFileHandler::class => new RotatingFileHandler(
                $handler_options['filename'] ?? throw new InvalidHandlerConfiguration('Missing Rotating File Handler Filename'),
                $handler_options['max_files'] ?? 7,
                $handler_options['level'],
                $handler_options['bubble'],
                $handler_options['file_permission'] ?? null,
                $handler_options['use_locking'] ?? false,
                $handler_options['date_format'] ?? RotatingFileHandler::FILE_PER_DAY,
                $handler_options['filename_format'] ?? '{filename}-{date}',
            ),
            StreamHandler::class => new StreamHandler(
                $handler_options['stream'] ?? throw new InvalidHandlerConfiguration('Missing Stream Handler Stream/Path'),
                $handler_options['level'],
                $handler_options['bubble'],
                $handler_options['file_permission'] ?? null,
                $handler_options['use_locking'] ?? false,
                $handler_options['file_open_mode'] ?? FileMode::WriteOnlyCreateNewOrAppendExisting->value,
            ),
            SlackWebhookHandler::class => new SlackWebhookHandler(
                $handler_options['webhook_url'] ?? throw new InvalidHandlerConfiguration('Missing Slack Webhook URL'),
                $handler_options['channel'] ?? null,
                $handler_options['username'] ?? null,
                $handler_options['use_attachment'] ?? true,
                $handler_options['icon_emoji'] ?? null,
                $handler_options['use_short_attachment'] ?? false,
                $handler_options['include_context_and_extra'] ?? false,
                $handler_options['level'],
                $handler_options['bubble'],
                $handler_options['exclude_fields'] ?? [],
            ),
            ErrorLogHandler::class => new ErrorLogHandler(
                $handler_options['message_type'],
                $handler_options['level'],
                $handler_options['bubble'],
                $handler_options['expand_newlines'],
            ),
            TestHandler::class => new TestHandler(
                $handler_options['level'],
                $handler_options['bubble'],
            ),
            NullHandler::class => new NullHandler(
                $handler_options['level'],
            ),
            NoopHandler::class => new NoopHandler(),
            default => throw new \UnexpectedValueException("Unsupported Handler Class: $handler_class"),
        };

        if (! $handler instanceof FormattableHandlerInterface) {
            return $handler;
        }

        $formatter = match ($formatter_class ?? self::DEFAULT_FORMATTERS[$handler_class] ?? LineFormatter::class) {
            LineFormatter::class => new LineFormatter(
                $formatter_options['format'] ?? null,
                $formatter_options['date_format'] ?? null,
                $formatter_options['allow_inline_line_breaks'] ?? false,
                $formatter_options['ignore_empty_context_and_extra'] ?? false,
                $formatter_options['include_stacktraces'] ?? false,
            ),
            LogglyFormatter::class => new LogglyFormatter(
                $formatter_options['batch_mode'] ?? JsonFormatter::BATCH_MODE_NEWLINES,
                $formatter_options['append_new_line'] ?? false,
            ),
            JsonFormatter::class => new JsonFormatter(
                $formatter_options['batch_mode'] ?? JsonFormatter::BATCH_MODE_NEWLINES,
                $formatter_options['append_new_line'] ?? true,
                $formatter_options['ignore_empty_context_and_extra'] ?? false,
                $formatter_options['include_stacktraces'] ?? false,
            ),
            default => throw new \UnexpectedValueException("Unsupported Formatter Class: $handler_class"),
        };

        $handler->setFormatter($formatter);

        return $handler;
    }
}
