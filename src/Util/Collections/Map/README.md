# Dictionary

Name TBD

In contrast to "collections", which are _lists_ of items, a "map" is 
a key to value _mapping_ of items, like an associative array. Some behaviors
are common to both collections and maps, such as iteration, filtering, and
transforming. However, there are some behaviors that are optimized when using a
maps (e.g. value lookups, key existence, etc.) and some that don't make
sense (e.g. sorting). Maps have overlap with PSR-11 containers.

- Unlike collections, maps are not ordered.
- Maps must have string keys
- Maps may have any type of value, including null
- Maps must be finite (in contrast to a collection which may generated from an iterator)