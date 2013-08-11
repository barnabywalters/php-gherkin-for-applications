<?php

require __DIR__ . '/../../vendor/autoload.php';

use Doctrine\Common\Annotations;

$project = realpath(__DIR__ . '/../../project');

$code = file_get_contents($project . '/index.web');

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
				echo "IN NAMESPACE";
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
			$solvers[$step] = $solver;
		}
	}
	
	return $solvers;
}

$file = __DIR__ . '/../BarnabyWalters/DefaultSolvers.php';
include $file;
$solvers = solversForFile($file);

print_r($solvers);
