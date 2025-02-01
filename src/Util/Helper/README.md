# Helper Function Classes

Classes in this directory provide generic utility functions namespaced into
classes that might otherwise be declared into global scope. By grouping the
functions, not only by the namespace, but by class, we get a few benefits.
Foremost, we can leverage lazy file autoloading via Composer, since PHP does
support function autoloading, otherwise, we would have to include the helper
files on each request using Composer's eager file autoloading functionality.

The methods defined in these classes should be succinct, pure functions without
any bound scope -- that is, every method should be `static`, as well as `final`.
This will give us the most flexibility to reuse small blocks of functionality 
throughout the framework, and to use the methods as callbacks. Following, the 
classes in this directory should not be instantiable, as there should not be any concept of
object scope between method calls. This can be achieved by declaring the class
as `abstract` and `readonly` The methods should operate on "lower level" things 
like primitive types and interfaces, as opposed to higher level objects like
entities.

Since these functions are expected to be frequently used through the codebase
over time and composed together with other functions, the names of the classes
and methods should be kept short for better readability and shorter lines of
code. On the other hand, each method should be fully documented, tested, and commented.
