# Utilities and Common Services

Note: Most things in this directory should not need to be defined by a service provider
as these are things that are consumed by services , and would not be expected to be
resolved in a certain configuration before.

We may eventually split this namespace into its own package, in order to use it
as a dependency of existing, legacy projects. That would provide a refactor path
for standardizing behaviors with Salt-Lite without requiring the legacy project
to be compatible with the transitive dependencies of the framework as a whole.
To that end, classes in the `PhoneBurner\SaltLite\Framework\Util` namespace should
avoid using third-party packages where possible.

Exceptions:
- Any class or interface defined in a PSR package
- `symfony/varexporter`


## Collections, Maps, and Containers
- Container: a structure that holds items
  - The entry identifer must be a non-empty string that uniquely identifies an item in the container
  - 