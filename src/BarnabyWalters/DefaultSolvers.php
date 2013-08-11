<?php

namespace BarnabyWalters\Solvers;

/** STEP show (.+) **/
function show($name, array $context) {
	echo "TODO: show the contents of the {$name} template, rendered with context:\n";
	print_r($context);
	return $context;
}

/**
 * STEP set (.+) to "(.+)"
 * STEP set (.+) to (\d+)
 */
function setContextProperty($name, $value, $context) {
	$context[$name] = $value;
	return $context;
}

/**
 * STEP make (.+) lowercase
 */
function toLowercase($name, $context) {
	if (isset($context[$name]) and is_string($context[$name]))
		$context[$name] = strtolower($context[$name]);
	
	return $context;
}
