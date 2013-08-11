php-gherkin-for-applications
============================

Prototype of some ideas I’ve had for a while, specced out here: https://gist.github.com/barnabywalters/6188240

Basic idea: Gherkin but for defining application logic. The “language” has almost no keywords, currently only “on” to define an event handler. Event names and step names are all strings which are matched to functions via regular expressions in the functions’s doc comments.

For example, this file:

```
on GET /
	show homepage
```

Along with this solver function file:

```php
<?php

namespace BarnabyWalters\Solvers;

/**
 * STEP show (.+)
 */
function showTemplate($name, array $context) {
	// assume renderTemplate exists
	echo renderTemplate("/templates/{$name}.html.php", $context);
	return $context;
}

```

and a simple HTML file a /templates/homepage.html.php, would create a simple web application which renders a template when you request the homepage.

Multiple steps within a single handler can be called. Regex subpatterns from step definitions are automatically injected into the function call. Each solver function can do two things: produce side effects (e.g. echoing content) and modify $context. The $context array returned from each step is passed into the next step.

Perhaps a $global read-only array-like param could also be given to solver functions, containing things like config information and global services like logging — although it would be nice if this wasn’t necessary.

Initially the idea is that a project could be packaged up into a single .phar file with dependencies and config info all inside, making the application easy to move around.

More interesting would be creating a UI to allow these applications to be developed interactively. More on that later.