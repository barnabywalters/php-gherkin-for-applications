<?php

require __DIR__ . '/../../vendor/autoload.php';

use Doctrine\Common\Annotations;

function functionsForFile($file) {
	$source = file_get_contents($file);
	$tokens = token_get_all($source);
	
	//print_r($tokens);
	
	$functions = array();
	$nextStringIsFunc = false;
	$inNamespace = true;
	$inClass = false;
	$bracesCount = 0;

	foreach ($tokens as $token) {
		switch ($token[0]) {
			case T_NAMESPACE:
				$inNamespace = true;
				$namespace = '';
				break;
			case T_CLASS:
				$inClass = true;
				break;
			case T_FUNCTION:
				if (!$inClass) {
					$nextStringIsFunc = true;
				}
				break;

			case T_STRING:
				if ($nextStringIsFunc) {
					$nextStringIsFunc = false;
					$functions[] = $namespace . '\\' . $token[1];
				} elseif ($inNamespace) {
					$namespace .= $token[1];
				}
				break;
			
			case T_NS_SEPARATOR:
				if ($inNamespace) {
					$namespace .= '\\';
				}
				break;
				
			// Anonymous functions
			case '(':
			case ';':
				$nextStringIsFunc = false;
				$inNamespace = false;
				break;

			// Exclude Classes
			case '{':
				if ($inClass)
					$bracesCount++;
				break;

			case '}':
				if ($inClass) {
					$bracesCount--;
					if ($bracesCount === 0)
						$inClass = false;
				}
				break;
		}
	}
	
	return $functions;
}

function stepsForFunction($func) {
	$function = new ReflectionFunction($func);
	$docComment = array_map(function ($s) {
			// If single-line doc comment, remove *?*/
			if (strrpos($s, '*/') == mb_strlen($s) - 2)
				$s = substr($s, 0, mb_strlen($s) - 2);
			elseif (strrpos($s, '**/') == mb_strlen($s) - 3)
				$s = substr($s, 0, mb_strlen($s) - 3);
			
			return ltrim($s, ' */');
		}, explode("\n", $function->getDocComment()));
	
	return array_map(function ($s) {
			return trim(substr($s, 4));
		}, array_filter($docComment, function ($s) {
				return substr($s, 0, 4) == 'STEP';
			}));
}

function solversForFile($file) {
	$functions = functionsForFile($file);
	
	$solvers = [];
	
	foreach ($functions as $f) {
		foreach (stepsForFunction($f) as $step) {
			$solvers[$step] = $f;
		}
	}
	
	return $solvers;
}

function stepsForEvent($matchEvent, $code) {
	$events = array_map('trim', array_filter(explode("\n\n", $code)));
	
	foreach ($events as $event) {
		if (strpos($event, "on {$matchEvent}") !== false) {
			$matchingEvent = $event;
			break;
		}
	}
	
	if (empty($matchingEvent))
		return [];
	
	return array_slice(array_map('trim', explode("\n", $matchingEvent)), 1);
}

function solve($step, $solvers, $context) {
	foreach ($solvers as $match => $solver) {
		$matches = [];
		
		if (preg_match('/' . $match . '/', $step, $matches) > 0)
			break;
	}
	
	if (empty($matches))
		return $context;
	
	$argCount = count($matches) - 1;
	
	if ($argCount > 0) {
		$args = array_slice($matches, 1);
		$args[] = $context;
		
		$function = new ReflectionFunction($solver);
		return $function->invokeArgs($args);
	} else {
		return $solver($context);
	}
}

// end functions, start output

// Set up
$project = realpath(__DIR__ . '/../../project');
$code = file_get_contents($project . '/index.web');

$file = __DIR__ . '/../BarnabyWalters/DefaultSolvers.php';
include $file;
$solvers = solversForFile($file);

// fake web server environment
$requestPath = '/';
$requestMethod = 'GET';

$context = [];

// TODO: match the request path against a set of steps

$steps = stepsForEvent("{$requestMethod} {$requestPath}", $code);

foreach ($steps as $step) {
	$context = solve($step, $solvers, $context);
}
