<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\TransformerStrategies;

use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\HttpExceptionResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\HttpExceptionResponseTransformerStrategy;
use PhoneBurner\SaltLite\Framework\Http\Response\HtmlResponse;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use Psr\Http\Message\ServerRequestInterface;

class HtmlResponseTransformerStrategy implements HttpExceptionResponseTransformerStrategy
{
    public function transform(
        HttpExceptionResponse $exception,
        ServerRequestInterface $request,
        LogTrace $log_trace,
    ): HtmlResponse {
        return new HtmlResponse($this->render($exception, $log_trace), $exception->getStatusCode());
    }

    private function render(HttpExceptionResponse $exception, LogTrace $log_trace): string
    {
        return <<<HTML
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;1,300&display=swap" rel="stylesheet">
                <title>{$exception->getStatusTitle()}</title>
                <style>
                    html,
                    body,
                    div.container {
                        height: 100%;
                    }
                    html,
                    body {
                        background-color: #f3f3f3;
                        color: #58595B;
                        font-family: "montserrat", sans-serif;
                    }
                    a,
                    .btn {
                        color: #58595B;
                        text-decoration: none;
                        border-bottom: 1px solid rgba(88, 89, 91, .28);
                        padding: 0;
                        border-radius: 0;
                    }
                    a:hover,
                    .btn:hover {
                        color: #000;
                        text-decoration: none;
                        border-bottom: 1px solid rgba(88, 89, 91, .8);
                    }
                    h1 {
                        font-weight: 700;
                        font-size: 2rem;
                    }
                    p {
                        font-size: 1.4rem;
                        font-weight: 300;
                    }
                    img, svg {
                        max-width: 100%;
                        height: 100px;
                    }
                    .btn-link {
                        font-weight: 700;
                        font-size: 1.3rem;
                    }
                    @media (min-width: 768px) {
                        h1 {
                            font-weight: 700;
                            font-size: 1.7rem;
                        }
                        p {
                            font-size: 1.4rem;
                            font-weight: 300;
                        }
                        img, svg {
                            max-width: 100%;
                            height: auto;
                        }
                        .btn-link {
                            font-weight: 700;
                            font-size: 1.2rem;
                        }
                    }
                    @media (min-width: 992px) {
                        h1 {
                            font-weight: 700;
                            font-size: 2rem;
                        }
                        p {
                            font-size: 1.7rem;
                            font-weight: 300;
                        }
                        img, svg {
                            max-width: 100%;
                        }
                        .btn-link {
                            font-weight: 700;
                            font-size: 1.5rem;
                        }
                    }
                    @media (min-width: 1200px) {
                        h1 {
                            font-weight: 700;
                            font-size: 2.5rem;
                        }
                        p {
                            font-size: 1.9rem;
                            font-weight: 300;
                        }
                        img, svg {
                            max-width: 100%;
                        }
                        .btn-link {
                            font-weight: 700;
                            font-size: 1.5rem;
                        }
                    }
                    .opacity-5 {
                        opacity: .5;
                    }
                </style>
            </head>
            <body>
            <div class="container d-block d-md-flex p-5 p-md-0">
                <div class="row justify-content-start align-items-center flex-grow-1">
                    <div class="col-12 col-sm-12 col-md-5 mb-5 mb-md-0">
                        <svg width="512px" height="448px" viewBox="0 0 512 448" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g id="404---gray" transform="translate(-152.000000, -281.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                    <g id="Group-3" transform="translate(151.000000, 280.000000)">
                                        <g id="Group-5" transform="translate(0.393975, 0.203250)">
                                            <text x="48%" y="65%" dominant-baseline="middle" text-anchor="middle" font-family="montserrat" font-size="160" font-weight="600" letter-spacing="-4.03931656">
                                                {$exception->getStatusCode()}
                                            </text>
                                            <g id="window" transform="translate(0.606025, 0.796750)">
                                                <path d="M69,84 C61.25625,84 55,77.74375 55,70 C55,62.25625 61.25625,56 69,56 C76.74375,56 83,62.25625 83,70 C83,77.74375 76.74375,84 69,84 Z M125,70 C125,62.25625 118.74375,56 111,56 C103.25625,56 97,62.25625 97,70 C97,77.74375 103.25625,84 111,84 C118.74375,84 125,77.74375 125,70 Z M167,70 C167,62.25625 160.74375,56 153,56 C145.25625,56 139,62.25625 139,70 C139,77.74375 145.25625,84 153,84 C160.74375,84 167,77.74375 167,70 Z M512,48 L512,400 C512,426.5 490.5,448 464,448 L48,448 C21.5,448 0,426.5 0,400 L0,48 C0,21.5 21.5,0 48,0 L464,0 C490.5,0 512,21.5 512,48 Z M480,143 L32,143 L32,396.5 C32,407.225 39.2,416 48,416 L464,416 C472.8,416 480,407.225 480,396.5 L480,143 Z M480,110 L480,41.75 C480,36.3875 472.8,32 464,32 L48,32 C39.2,32 32,36.3875 32,41.75 L32,110 L480,110 Z" id="Shape"></path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </svg>
                    </div>
                    <div class="col-12 col-sm-12 col-md-7">
                        <div class="p-md-2 p-lg-5">
                            <h1 class="mb-3">{$exception->getStatusTitle()}</h1>
                            <p class="mb-4">{$exception->getStatusDetail()}</p>
                            <div class="mt-5 font-weight-light text-muted small">
                                <span>Reference: {$log_trace}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </body>
            </html>
            HTML;
    }
}
