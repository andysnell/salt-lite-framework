# Salt-Lite Framework

> Feels like home, just without the salty tears of frustration

The Salt-Lite Framework is a "batteries-included" PHP framework, modeled on other
modern frameworks like Symfony and Laravel, derived from the original Salt project.
Ideally, it adapts the best core features of Salt without dragging along unnecessary
complexity, technical debt and design decisions we regret. The goal is to provide
users with a robust framework with minimum cognitive overhead from the original
Salt framework, avoiding the pitfalls of bringing in a full-fledged third-party
framework and trying to adapt that to our needs.

### Guiding Principles

1. PSRs are good, but not the end-all-be-all
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
- PHPUnit 11 is used for testing, with ParaTest for parallel execution, this is
  a significant upgrade from the previous version. Notably unit tests are defined
  with attributes and data providers must be defined as static functions.

### Backwards Capability Guarantees

Classes and interfaces with the `#[PhoneBurner\SaltLite\Framework\Util\Attribute\Contract]` attribute
are considered part of the public API of the framework and should not be changed without
a major version bump. These "contracts" can be freely used in application code.

Conversely, classes and interfaces with the `#[PhoneBurner\SaltLite\Framework\Util\Attribute\Internal]`
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