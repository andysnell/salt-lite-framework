<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer;

enum AttachmentType
{
    case AttachFromPath;
    case AttachFromContent;
    case EmbedFromPath;
    case EmbedFromContent;
}
