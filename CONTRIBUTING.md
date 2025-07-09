# Contributing

As an open source project, community contributions are welcome. This project accepts bug reports, bug fixes, and
discussion/requests for new and updated functionality on [GitHub]. We especially welcome pull requests that add tests
for existing bugs, improve documentation, or keep the project up to date with the latest version of PHP and other
dependencies. Please read the following guidelines before submitting a pull request.

## Reporting Bugs

> Please see the [Security Policy](SECURITY.md) for more information on reporting
> issues concerning project security and potential vulnerabilities issues.

You can find help and discussion at the project's GitHub [Issues] page.
Please use the issue tracker to report any bugs found with this project. When
submitting a bug report, please include enough information to reproduce the
bug. A good bug report includes the following sections:

- **Description**: Provide a short and clear description of the bug.
- **Steps to reproduce**: Provide steps to reproduce the behavior you are experiencing. Please try to
  keep this as short as possible. If able, create a reproducible script (agnostic
  of any particular framework) you are using. This will help us to quickly debug the issue.
- **Expected behavior**: Provide a short and clear description of what you expect to happen.
- **Screenshots or output**: If applicable, add screenshots or program output to help explain your problem.
- **Environment details**: Provide details about the system where you're using this package, such as PHP
  version and operating system.
- **Additional context**: Provide any additional context that may help us debug the problem.

⚠️ _**DO NOT include passwords or other sensitive information in your bug report.**_

## Fixing Bugs

If you see an open bug report that you'd like to fix, please feel free to do so.
Following the directions and guidelines as described below in the "Adding New Features"
section below, you may create bugfix branches and send pull requests. Please be
sure to reference the issue number of the bug in your pull request description.

Pull requests for bugs related to non-test code SHOULD include unit tests that verify the fix and
prevent future regressions.

## Adding New Features

If you have an idea for a new feature, please check out the open and closed
[Issues] and active [Pull Requests] first to see if the feature is currently under
discussion, development, or if it has already been rejected.

Unless the changes are trivial, like adding documentation or fixing a typo, please
open an issue first to discuss the feature before you submit a pull request. This
helps us to understand the feature, scope of change, and ensure the correct
semantic versioning of the project. It also allows discussion of the _feature_to
live separately from the pull request \_implementing_ the feature, as it might take
a couple multiple iterations to get the feature right.

Please recognize that some features won't fit with the goals of this project and
that we don't enjoy rejecting anyone's hard work. If the feature is something you
feel very strongly about, please feel free to fork the project and implement it
(as per the terms of the project [LICENSE]).

Pull requests for new and updated functionality MUST include unit tests that cover
the "happy paths" and the "unhappy paths" of the changeset. Unit tests are
a kind of documentation for the code, and well-written tests will help us to understand
how to use your contribution and what to expect from it.

## Project Conventions, Coding Standards, and Style Guidelines

All contributions to this project MUST follow the following guidelines. Please
ensure that your code adheres to these guidelines _before_ submitting a pull request
by running the test and code quality tools (see the "Local Development and Tooling" section below).

### Coding Style

This project follows the [PhoneBurner PHP Coding Standard], which is a superset of
the [PER Coding Style 2.0][per-cs] standard. Style rules are mainly enforced with
PHP_CodeSniffer and Rector. See the [.editorconfig], [phpcs.xml], and [rector.php] files in the
root of the project.

#### Naming Conventions

- Variable, property, and parameter names, except for those directly shadowing a third party package, MUST be in
  snake_case.
- Anonymous and arrow functions MUST have a defined parameter and return types
- Enum cases names MUST be in PascalCase, unless they are less than 4, in which case they MAY be in UPPER_SNAKE_CASE.
  For example, either `State::Ohio` or `State::OH`, not `State::OHIO` or `State::Oh`.

#### Type Declarations

- All PHP files must have `declare(strict_types=1);` at the top of the file.
- All functions and methods MUST have well-defined parameter types.
- All functions and methods, except for class `__construct` and `__destruct` methods MUST have well-defined return
  types.
- All arrow and anonymous functions MUST have well-defined parameter and return types.
- All class properties MUST have well-defined types.
- Nullable types MUST be explicitly defined as such. For example, `?string $name` instead of `string|null $name`.
- If a class property can be `readonly`, it SHOULD be defined as such.
- Constructor property promotion SHOULD be used where possible.
- "Domain" classes (i.e., classes that _are things_, the kind that would not be mocked in unit tests) MAY be defined
  with the `final` or `readonly` keywords.
- "Service" classes (i.e., classes that _do things_, the kind that would be mocked in unit tests) SHOULD NOT be defined
  with the `final` or `readonly` keywords.

#### Comments

- Comments SHOULD be meaningful, concise, and MUST be written in grammatically correct English. A good comment will
  explain the "why" of the code, not the "what". For example, `// This is a singleton class` is not a good comment,
  whereas `// This class is a singleton to ensure that only one instance of the database connection is created` is more
  helpful.
- Avoid useless or redundant comments such as a type annotation that exactly matches the type declaration or
  `// Increment i by 1` for `++$i`
- Remove "intermediate" or prompt comments created by or for generative AI tools that do not add meaningful value to the
  code.

#### Unit Tests

- Unit test files MUST be suffixed with `Test.php` and located in the `tests` directory, shadowing the
  directory structure of the source code. For example, a class file located at
  "src/Container/Domain/Example.php" should have a corresponding unit test file
  "tests/Container/Domain/ExampleTest.php".
- Unit tests MUST be defined with the `[#\PHPUnit\Framework\Attributes\TestTest]`
  attribute instead of the `@test` annotation or `test*` method prefix.

#### Pull Request Guidelines

- **Use topic/feature branches.** This project follows "[GitHub Flow]" for pull requests.
- **One feature per pull request.** Please break multiple features and into separate pull requests.
- **Keep the commit history clean.** Rebase the feature branch on the latest upstream target branch before submitting your pull
  request. Squash commits into one or more atomic logical changeset commits before submitting your pull request. (Even a
  large feature pull request should only have a few commits.)
- **Use descriptive commit messages.** The subject line should be short and use the imperative mood. The body should
  include the motivation for the change and contrast with the previous behavior, and any other relevant information.
- **Open pull requests with detailed descriptions.**: Your pull request description should clearly detail changes you have made. We will
  use this description to update the CHANGELOG.

## Local Development and Tooling

See the project [composer.json] file for the environment and dependency requirements.
This project has a standardized [Dockerfile] defined development image, and we recommended that it be used for all
development and testing. For convenience, the project includes a `docker-compose.yml` file and a `Makefile` to simplify/standardize the
development process and tooling.

#### Installation and Environment Setup

> Note: This guide assumes your host environment is Unix-like (Linux, macOS, WSL2), and that Docker is already installed.

Fork and clone this repository locally, navigate to the project root, and run the
(i.e. `cd /path/to/repository`) and then execute the following command to build
the Docker image, create build files, and install Composer dependencies:

```bash
make
```

While Composer is configured as a script-runner inside the container, `make`
is used externally from the host environment to run most of the common scripts.

```bash
# Run all tests and coding standards checks required to pass before a pull request can be accepted
make ci

# Run PHPStan to statically analyze the entire codebase
make phpstan

# Run the PHP syntax linter
make lint

# Run the PHP_CodeSniffer code standards linter
make phpcs

# Attempt to auto-fix coding standards issues found by phpcs
make phpbf

# Run Rector with project configuration, without making changes
make rector-dry-run

# Run Rector with the project configuration and apply automated fixes
make rector

# Run PHPUnit tests
make phpunit
```

<!-- References -->

[GitHub]: https://github.com/phoneburner/salt-lite-framework
[Issues]: https://github.com/phoneburner/salt-lite-framework/issues
[Pull Requests]: https://github.com/phoneburner/salt-lite-framework/pulls
[per-cs]: https://www.php-fig.org/per/coding-style/
[GitHub Flow]: https://guides.github.com/introduction/flow/
[PhoneBurner PHP Coding Standard]: https://github.com/PhoneBurnerOpenSource/php-coding-standard
