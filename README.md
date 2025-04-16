<p align="center"><a href="https://github.com/phoneburner/salt-lite-framework" target="_blank">
<img src="public/images/salt-lite-logo.svg" width="350" alt="Logo"/>
</a></p>

# SaltLite Framework

> Feels like home, just without the salty tears of frustration

The SaltLite Framework is a "batteries-included", very-highly-opinionated PHP 
framework, derived from the original Salt framework/application used by PhoneBurner.
While modeled on other modern "general purpose" frameworks like Symfony and Laravel, 
the SaltLite Framework is designed and optimized as an API backend. 

Ideally, it adapts the best core features of Salt without dragging along unnecessary
complexity, technical debt, and the (many) design decisions we regret. The goal is
to provide users with a robust framework with minimum cognitive overhead from the original
Salt framework, avoiding the pitfalls of bringing in a full-fledged third-party
framework and trying to adapt that to our needs.

### Guiding Principles

1. Compatiblity with the PSRs should be the general rule, but sensible deviations are allowed, especially in the name of type safety.
2. Where practical, third-party library code should be wrapped in a way that lets us expose our own interface. This
   allows us to swap out the underlying library without changing application code.
3. Separation of "framework" and "application" concerns
4. Take the best parts of Salt, leave the rest, and add new features wrapping the best of modern PHP
5. Configuration driven, with environment variables as the primary source of overrides

### Notable Differences from Salt

- The time zone configuration for PHP and the database is set to UTC by default.
- Configuration is defined by the environment, and not by the path value of a request.
- Overriding configuration values is done via environment variables, not by adding local configuration files.
- Database migrations are handled by
  the [Doctrine Migrations](https://www.doctrine-project.org/projects/migrations.html) library, as opposed to Phinx.
- PHPUnit 12 is used for testing, this is
  a significant upgrade from the previous version. Notably unit tests are defined
  with attributes and data providers must be defined as static functions.
- When cast to a string, `\PhoneBurner\SaltLite\Domain\PhoneNumber\DomesticPhoneNumber` is formatted as an
  E.164 phone number ("+13145551234"), instead a ten-digit number ("3145551234").

### Backwards Capability Guarantees

Classes and interfaces with the `#[PhoneBurner\SaltLite\Attribute\Usage\Contract]` attribute
are considered part of the public API of the framework and should not be changed without
a major version bump. These "contracts" can be freely used in application code.

Conversely, classes and interfaces with the `#[PhoneBurner\SaltLite\Attribute\Usage\Internal]`
attribute are very tightly coupled to third-party vendor and/or framework logic,
and should not be used in application code.

### Included Functionality

- PSR-7/PSR-15 Request & Response Handling
- PSR-11 Dependency Injection Container
- PSR-3 Logging with Monolog
- PSR-14 Event Dispatching based on Symfony EventDispatcher
- Local/Remote Filesystem Operations with Flysystem
- Development Environment Error Handling with Whoops
- Console Commands with Symfony Console
- Interactive PsySH Shell with Application Runtime
- Doctrine ORM & Migrations
- Redis for Remote Caching with PSR-6/PSR-16 Support
- RabbitMQ for Message Queues and Job Processing
- Task Scheduling with Cron Expression Parsing with Symfony Scheduler
- SMTP/API Email Sending with Symfony Mailer

### Conventions

- Component Namespaces like `PhoneBurner\SaltLite\Framework\Database` should represent
a cohesive "component".
- Each Component namespace MAY have a Service Provider class, which is responsible for
registering related services for that component and any subcomponents with the DI container.
Non-optional framework level service providers MUST be listed in the
`\PhoneBurner\SaltLite\Framework\Container\ContainerFactory::FRAMEWORK_PROVIDERS` array.
- Each Component namespace MAY have a configuration file, the name of which should be
component in kabob-case, e.g. `database.php` or `message-bus.php` This file should 
return an array of configuration values, with a single top-level key. That key 
MUST be the component name in snake case, e.g. `'database'` or `'message_bus'`.