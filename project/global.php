<?php

// Creates the global read-only context — usually this is config variables and 
// services and things.
// Maybe just add some sane defaults (e.g. path, domain, request) in here instead
// of automatically adding them elsewhere. Allows people to customise much more 
// easily.

// Should we have some way of having conditional global evaluations? So things 
// which aren’t needed for everything aren’t recreated on every request. Perhaps
// do that through lazy loading.

// Idea: define functions here which return objects when they’re needed. Kinda 
// like dep. injection containers.

use Symfony\Component\HttpFoundation as Http;
use Taproot\Librarian;

$request = Http\Request::createFromGlobals();
$projectPath = __DIR__;
$notes = new Librarian\Librarian();

return [
	'notes' => $notes,
	'request' => $request,
	'path' => $projectPath,
];
