<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response;

use Crell\ApiProblem\ApiProblem;
use PhoneBurner\SaltLite\Framework\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;

class ApiProblemResponse extends JsonResponse
{
    public function __construct(int $status, string $title, iterable $additional = [])
    {
        $problem = new ApiProblem($title, 'https://httpstatuses.io/' . $status);
        $problem->setStatus($status);
        foreach ($additional as $key => $value) {
            $problem[$key] = $value;
        }

        parent::__construct($problem->asArray(), $problem->getStatus(), [
            HttpHeader::CONTENT_TYPE => ContentType::PROBLEM_DETAILS_JSON,
        ]);
    }
}
