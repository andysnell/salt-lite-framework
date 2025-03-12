<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\LogglyFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FallbackGroupHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\NoopHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Container\ServiceFactory;
use PhoneBurner\SaltLite\Filesystem\FileMode;
use PhoneBurner\SaltLite\Framework\Logging\Config\LoggingConfigStruct;
use PhoneBurner\SaltLite\Framework\Logging\Config\LoggingHandlerConfigStruct;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Exception\InvalidHandlerConfiguration;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\LoggerExceptionHandler;
use PhoneBurner\SaltLite\Framework\MessageBus\LongRunningProcessServiceResetter;
use PhoneBurner\SaltLite\Logging\PsrLoggerAdapter;
use PhoneBurner\SaltLite\String\Str;
use PhoneBurner\SaltLite\Type\Type;
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

    public HandlerInterface|null $fallback_handler = null;

    public function __invoke(App $app, string $id): LoggerInterface
    {
        return new PsrLoggerAdapter(ghost(function (Logger $logger) use ($app): void {
            $config = Type::of(LoggingConfigStruct::class, $app->config->get('logging'));

            // Configure the fallback handler to use when an error is encountered while
            // processing a log entry. Defaults to the noop handler, which does nothing.
            $fallback_handler = $this->handler($config->fallback_handler);

            // Wrap each handler in a FallbackGroupHandler to ensure that any error
            // while writing to the log do not break the application, but still try
            // to log it somewhere.
            $handlers = \array_map(fn(LoggingHandlerConfigStruct $handler_config): HandlerInterface => new FallbackGroupHandler([
                $this->handler($handler_config),
                $fallback_handler,
            ]), \array_values($app->config->get('logging.handlers')));

            $logger->__construct(
                $app->config->get('logging.channel') ?? Str::kabob($app->config->get('app.name')),
                $handlers,
                \array_map($app->services->get(...), $app->config->get('logging.processors') ?? []),
            );

            $logger->setExceptionHandler($app->get(LoggerExceptionHandler::class)(...));

            // On resolution, replace the resolved logger as the container's
            // logger instance, which should also consume any buffered log
            // entries from the default buffer logger.
            $app->services->setLogger($logger);

            // Register with the long-running process service resetter to make sure
            // that we batch/flush any buffered log entries when the worker stops.
            $app->services->get(LongRunningProcessServiceResetter::class)->add($logger, 'reset');
        }));
    }

    private function handler(
        LoggingHandlerConfigStruct $config,
    ): HandlerInterface {
        $handler = match ($config->handler_class) {
            LogglyHandler::class => new LogglyHandler(
                $config->handler_options['token'] ?? throw new InvalidHandlerConfiguration('Missing Loggly API Token'),
                Level::from($config->level->toMonlogLogLevel()),
                $config->bubble,
            ),
            RotatingFileHandler::class => new RotatingFileHandler(
                $config->handler_options['filename'] ?? throw new InvalidHandlerConfiguration('Missing Rotating File Handler Filename'),
                $config->handler_options['max_files'] ?? 7,
                Level::from($config->level->toMonlogLogLevel()),
                $config->bubble,
                $config->handler_options['file_permission'] ?? null,
                $config->handler_options['use_locking'] ?? false,
                $config->handler_options['date_format'] ?? RotatingFileHandler::FILE_PER_DAY,
                $config->handler_options['filename_format'] ?? '{filename}-{date}',
            ),
            StreamHandler::class => new StreamHandler(
                $config->handler_options['stream'] ?? throw new InvalidHandlerConfiguration('Missing Stream Handler Stream/Path'),
                Level::from($config->level->toMonlogLogLevel()),
                $config->bubble,
                $config->handler_options['file_permission'] ?? null,
                $config->handler_options['use_locking'] ?? false,
                $config->handler_options['file_open_mode'] ?? FileMode::WriteCreateOrAppendExisting->value,
            ),
            SlackWebhookHandler::class => new SlackWebhookHandler(
                $config->handler_options['webhook_url'] ?? throw new InvalidHandlerConfiguration('Missing Slack Webhook URL'),
                $config->handler_options['channel'] ?? null,
                $config->handler_options['username'] ?? null,
                $config->handler_options['use_attachment'] ?? true,
                $config->handler_options['icon_emoji'] ?? null,
                $config->handler_options['use_short_attachment'] ?? false,
                $config->handler_options['include_context_and_extra'] ?? false,
                Level::from($config->level->toMonlogLogLevel()),
                $config->bubble,
                $config->handler_options['exclude_fields'] ?? [],
            ),
            ErrorLogHandler::class => new ErrorLogHandler(
                $config->handler_options['message_type'] ?? ErrorLogHandler::OPERATING_SYSTEM,
                Level::from($config->level->toMonlogLogLevel()),
                $config->bubble,
                $config->handler_options['expand_newlines'] ?? false,
            ),
            TestHandler::class => new TestHandler(
                Level::from($config->level->toMonlogLogLevel()),
                $config->bubble,
            ),
            NullHandler::class => new NullHandler(
                Level::from($config->level->toMonlogLogLevel()),
            ),
            NoopHandler::class => new NoopHandler(),
            default => throw new \UnexpectedValueException('Unsupported Handler Class: ' . $config->handler_class),
        };

        if (! $handler instanceof FormattableHandlerInterface) {
            return $handler;
        }

        $formatter = match ($config->formatter_class ?? self::DEFAULT_FORMATTERS[$config->handler_class] ?? LineFormatter::class) {
            LineFormatter::class => new LineFormatter(
                $config->formatter_options['format'] ?? null,
                $config->formatter_options['date_format'] ?? null,
                $config->formatter_options['allow_inline_line_breaks'] ?? false,
                $config->formatter_options['ignore_empty_context_and_extra'] ?? false,
                $config->formatter_options['include_stacktraces'] ?? false,
            ),
            LogglyFormatter::class => new LogglyFormatter(
                $config->formatter_options['batch_mode'] ?? JsonFormatter::BATCH_MODE_NEWLINES,
                $config->formatter_options['append_new_line'] ?? false,
            ),
            JsonFormatter::class => new JsonFormatter(
                $config->formatter_options['batch_mode'] ?? JsonFormatter::BATCH_MODE_NEWLINES,
                $config->formatter_options['append_new_line'] ?? true,
                $config->formatter_options['ignore_empty_context_and_extra'] ?? false,
                $config->formatter_options['include_stacktraces'] ?? false,
            ),
            default => throw new \UnexpectedValueException('Unsupported Formatter Class: ' . $config->formatter_class),
        };

        $handler->setFormatter($formatter);

        return $handler;
    }
}
