<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Domain;

final class ContentType
{
    public const string JSON = 'application/json';
    public const string HAL_JSON = 'application/hal+json';
    public const string HEALTH_JSON = 'application/health+json';
    public const string PNG = 'image/png';
    public const string HTML = 'text/html';
    public const string PROBLEM_DETAILS_JSON = 'application/problem+json';
    public const string TEXT = 'text/plain';
    public const string CSV = 'text/csv';
    public const string OCTET_STREAM = 'application/octet-stream';
    public const string ZIP = 'application/zip';
    public const string PHP = 'application/x-php';
    public const string GIF = 'image/gif';
    public const string CSS = 'text/css';
    public const string JS = 'text/javascript';
    public const string AIFF = 'audio/x-aiff';
    public const string AVI = 'video/avi';
    public const string BMP = 'image/bmp';
    public const string BZ2 = 'application/x-bz2';
    public const string DMG = 'application/x-apple-diskimage';
    public const string DOC = 'application/msword';
    public const string DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    public const string JPEG = 'image/jpeg';
    public const string FLV = 'video/x-flv';
    public const string GZ = 'application/gzip';
    public const string EML = 'message/rfc822';
    public const string PS = 'application/postscript';
    public const string XML = 'application/xml';
    public const string XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    public const string WAV = 'audio/wav';
    public const string XLS = 'application/excel';
    public const string WMV = 'audio/x-ms-wmv';
    public const string WMA = 'audio/x-ms-wma';
    public const string VCF = 'text/x-vcard';
    public const string TTF = 'application/x-font-truetype';
    public const string TIFF = 'image/tiff';
    public const string SVG = 'image/svg+xml';
    public const string SIT = 'application/x-stuffit';
    public const string TAR = 'application/x-tar';
    public const string RTF = 'application/rtf';
    public const string RAR = 'application/x-rar-compressed';
    public const string PPTX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    public const string PPT = 'application/vnd.ms-powerpoint';
    public const string PDF = 'application/pdf';
    public const string OGG = 'audio/ogg';
    public const string ODS = 'application/vnd.oasis.opendocument.spreadsheet';
    public const string ODT = 'application/vnd.oasis.opendocument.text';
    public const string ODP = 'application/vnd.oasis.opendocument.presentation';
    public const string ODG = 'application/vnd.oasis.opendocument.graphics';
    public const string MP3 = 'audio/mpeg';
    public const string MP4 = 'video/mp4';
    public const string MPEG = 'video/mpeg';
    public const string MOV = 'video/quicktime';
    public const string MIDI = 'audio/midi';
    public const string EXE = 'application/x-ms-dos-executable';
    public const string HQX = 'application/stuffit';
    public const string JAR = 'application/x-java-archive';
    public const string M3U = 'audio/x-mpegurl';
    public const string M4A = 'audio/mp4';
    public const string MDB = 'application/x-msaccess';
    public const string ICO = 'image/x-icon';
    public const string YAML = 'application/yaml';
    public const string ICS = 'text/calendar';

    public static function lookup(string $extension): string|null
    {
        return match ($extension) {
            'php', 'php3', 'php4', 'php5' => self::PHP,
            'zip' => self::ZIP,
            'gif' => self::GIF,
            'png' => self::PNG,
            'css' => self::CSS,
            'js' => self::JS,
            'txt' => self::TEXT,
            'aif', 'aiff' => self::AIFF,
            'avi' => self::AVI,
            'bmp' => self::BMP,
            'bz2' => self::BZ2,
            'csv' => self::CSV,
            'dmg' => self::DMG,
            'doc' => self::DOC,
            'docx' => self::DOCX,
            'eml' => self::EML,
            'eps', 'ps' => self::PS,
            'exe' => self::EXE,
            'flv' => self::FLV,
            'gz' => self::GZ,
            'hqx' => self::HQX,
            'htm', 'html' => self::HTML,
            'ics' => self::ICS,
            'jar' => self::JAR,
            'jpeg', 'jpg' => self::JPEG,
            'json' => self::JSON,
            'm3u' => self::M3U,
            'm4a' => self::M4A,
            'mdb' => self::MDB,
            'mid', 'midi' => self::MIDI,
            'mov' => self::MOV,
            'mp3' => self::MP3,
            'mp4' => self::MP4,
            'mpeg', 'mpg' => self::MPEG,
            'odg' => self::ODG,
            'odp' => self::ODP,
            'odt' => self::ODT,
            'ods' => self::ODS,
            'ogg' => self::OGG,
            'pdf' => self::PDF,
            'ppt' => self::PPT,
            'pptx' => self::PPTX,
            'rar' => self::RAR,
            'rtf' => self::RTF,
            'tar' => self::TAR,
            'sit' => self::SIT,
            'svg' => self::SVG,
            'tif', 'tiff' => self::TIFF,
            'ttf' => self::TTF,
            'vcf' => self::VCF,
            'wav', 'sln' => self::WAV,
            'wma' => self::WMA,
            'wmv' => self::WMV,
            'xls' => self::XLS,
            'xlsx' => self::XLSX,
            'xml' => self::XML,
            'ico' => self::ICO,
            'yaml', 'yml' => self::YAML,
            'a', 'bin', 'bpk', 'deploy', 'dist', 'distz','dms', 'dump', 'elc', 'lha', 'lrf', 'lzh', 'o', 'obj', 'pkg', 'so' => self::OCTET_STREAM,
            default => null,
        };
    }
}
