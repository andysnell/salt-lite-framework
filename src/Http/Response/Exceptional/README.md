# Exceptional Responses

Standardized exceptions for common error states corresponding to HTTP statues to
throw in your application, that are also instances of `Psr\Http\Message\ResponseInterface`.

These exceptions wrap simple plain text responses, but can be transformed in
middleware to more complex, request-specific responses. That is, the same
exception/response can be used to generate an HTML response for a browser, or a
JSON response for an API client. 

By default, the transformed JSON response is formatted an 
[RFC 7807 Problem Details](https://datatracker.ietf.org/doc/html/rfc7807) response.
Additional attributes can be included when instantiating the exception.

HTTP 400 Bad Request: `BadRequestResponse`
HTTP 401 Unauthorized: `AuthenticationRequiredResponse`
HTTP 403 Forbidden : `AuthorizationRequiredResponse`
HTTP 404 Not Found: `PageNotFoundResponse` / `FileNotFoundResponse` / `ResourceNotFoundResponse` / `NotFoundResponse`
HTTP 405 Method Not Allowed : `MethodNotAllowedResponse`
HTTP 410 Gone: `DeadRouteResponse`
HTTP 429 Too Many Requests: `TooManyRequestsResponse`
HTTP 451 Unavailable For Legal Reasons: `UnavailableForLegalReasonsResponse`
HTTP 500 Internal Server Error: `ServerErrorResponse`
HTTP 501 Not Implemented: `NotImplementedResponse`
HTTP 503 Service Unavailable: `ServiceUnavailableResponse`

## Generic Usage

Any PSR-7 compatible response can be thrown as an exception by wrapping it with
`ResponseException`. This is useful for deep in your application where you want
to return a particular response, but can't directly to pass the response object
back into the middleware stack. This can even be used for HTTP 200 responses.

```php
use Psr\Http\Message\ResponseInterface;

$response = new Response(200, [], 'Hello, World!');

throw new ResponseException($response);
```

`\PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\GenericHttpExceptionResponse`
is a generic exception that can be used to throw any HTTP status code, with whatever
message you want. This is useful for when you want to throw a status code that is
not covered by the other exceptions.

```php
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\GenericHttpExceptionResponse;

throw new GenericHttpExceptionResponse(418, 'I am a teapot');
```