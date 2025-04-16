<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Storage\Config;

use League\Flysystem\AwsS3V3\VisibilityConverter;
use League\MimeTypeDetection\MimeTypeDetector;
use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;

final readonly class S3FilesystemConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param array<string, mixed> $client see \Aws\S3\S3Client for options and configuration
     * @param array<string, mixed> $options
     */
    public function __construct(
        public array $client = [
            'credentials' => [
                'key' => null,
                'secret' => null,
            ],
            'region' => 'us-west-1',
            'signature' => 'v4',
            'version' => 'latest',
        ],
        public string $bucket_name = '',
        public string $prefix = '',
        public VisibilityConverter|null $visibility_converter = null,
        public MimeTypeDetector|null $mime_type_detector = null,
        public array $options = [],
        public bool $stream_reads = true,
        public array|null $forwarded_options = null,
        public array|null $multipart_upload_options = null,
    ) {
    }
}
