<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\FastRoute;

use Brick\VarExporter\VarExporter;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedGenerator;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdRouteParser;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileWriter;
use Psr\Log\LoggerInterface;

#[Internal]
class FastRouteDispatcherFactory
{
    private const int EXPORT_OPTIONS = VarExporter::ADD_RETURN | VarExporter::TRAILING_COMMA_IN_ARRAY;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string|null $cache_file = null,
    ) {
    }

    /**
     * Note: In order to avoid a potential race condition between multiple execution
     * threads trying to write a new cache file to the same memory location on
     * when the file does not exist, and ending up with an unparsable route file,
     * we first write to a temporary file and then rename (i.e. `mv`) it to the
     * actual cache file. Since we're renaming a file on the same file system and in
     * the same directory, this should be an atomic operation and avoid permissions
     * issues. If multiple threads try to do this simultaneously, the last write
     * wins, while any attempts to read the file during the rename operation will
     * be successful using the file-to-be-overwritten.
     *
     * @param callable(RouteCollector): void $route_definition_callback
     */
    public function make(callable $route_definition_callback): Dispatcher
    {
        if ($this->cache_file && \file_exists($this->cache_file)) {
            try {
                return new GroupCountBasedDispatcher(require $this->cache_file);
            } catch (\Throwable $e) { // Includes \ParseError
                @\unlink($this->cache_file);
                $this->logger->critical('Route Cache File Read Failed', ['exception' => $e]);
            }
        }

        $route_collector = new RouteCollector(new StdRouteParser(), new GroupCountBasedGenerator());
        $route_definition_callback($route_collector);
        $dispatch_data = $route_collector->getData();

        if ($this->cache_file) {
            try {
                FileWriter::string($this->cache_file, '<?php ' . VarExporter::export($dispatch_data, self::EXPORT_OPTIONS));
            } catch (\Throwable $e) {
                $this->logger->critical('Route Cache File Write Failed', ['exception' => $e]);
            }
        }

        return new GroupCountBasedDispatcher($dispatch_data);
    }
}
