<?php

class Macros
{

	public static function setupMacros($compiler) {
		$set = new \Nette\Latte\Macros\MacroSet($compiler);
		$set->addMacro('iss','echo ' . __NAMESPACE__ . '\Macros::issCompile($basePath, %node.array);');
	}

	public static function setupTemplate($template, \MyTexy $texy)
	{
		$template->registerHelper('texy', callback($texy, 'process'));
		$template->registerHelper('texy_title', callback($texy, 'processTypo'));

		return $template;
    }

	public static function getWikiLink($page, $lang = NULL) {
		$anchor = NULL;

		$parts = explode('#', $page, 2);
		if (count($parts) === 2) {
			$page = $parts[0];
			$anchor = '#' . $parts[1];
		}

		if ($lang != NULL) {
			// fix languages with different code on wiki
			/*switch ($lang) {
				case 'cs':
					$lang = 'cz';
					break;
			}*/
			$page .= '/' . $lang;
		}

		return 'https://wiki.miranda-ng.org/index.php?title=' . $page . $anchor;
	}

	/**
	 * @param string
	 * @param string
	 * @return bool
	 */
	private static function issCheck($dir, $out) {
		if (!file_exists($out)) {
			return TRUE;
		}
		$time = filemtime($out);
		$it = new \DirectoryIterator($dir);
		foreach ($it as $file) {
			if ($file->isFile() && $file->getMTime() > $time) {
				return TRUE;
			}
		}
	}

	/**
	 * @param string
	 * @param string
	 * @return string
	 */
	public static function issCompile($basePath, $data)
	{
		$name = $data[0];
		$media = isset($data[1]) ? $data[1] : "screen,projection,tv";

		// FIXME: write it better
		$wwwDir = __DIR__ . "/../";

		$in = $wwwDir . "/iss/" . $name . ".iss";
		$dir = $wwwDir . "/css";
		if (!file_exists($dir) && !mkdir($dir))
			throw new \RuntimeException("Can't create '$dir' directory for css.");

		$out = $dir . '/' . $name . '.css';

		if (self::issCheck(dirname($in), $out)) {
			$ivory = new \Ivory\Compiler();
			$ivory->outputDirectory = $dir;
			//$ivory->setDefaultUnit('px');
			$ivory->addFunction('img', function (array $value) {
				if (isset($value[0]) && $value[0] == 'string') {
					return array('function', 'url', array(array('expression', array('string', "'" . "../images/" . "'"), array('binary', '.'), $value)));
				}
			});
			$ivory->addFunction('font', function (array $value) {
				if (isset($value[0]) && $value[0] == 'string') {
					return array('expression', array('string', "'" . "fonts/" . "'"), array('binary', '.'), $value);
				}
			});

			$ivory->addFunction('colorEdit', function (array $value, $color, $substract = false) {
				if (isset($value[0]) && $value[0] == 'color') {

					if ($substract) {
						return array(
							'color',
							$value[1] - $color[1],
							$value[2] - $color[2],
							$value[3] - $color[3],
							1
						);
					} else {
						return array(
							'color',
							$value[1] + $color[1],
							$value[2] + $color[2],
							$value[3] + $color[3],
							1
						);
					}
				}
			});

			$ivory->addFunction('colorAdd', function (array $value, $color) {
				if (isset($value[0]) && $value[0] == 'color') {

					return array(
						'color',
						min(max($value[1] + $color[1], 0),255),
						min(max($value[2] + $color[2], 0),255),
						min(max($value[3] + $color[3], 0),255),
						1
					);
				}
			});

			$ivory->compileFile($in);
		}

		$ret = \Nette\Utils\Html::el("link");
		$ret->rel = "stylesheet";
		$ret->href = $basePath . '/css/' . $name . '.css?t=' . filemtime($out);
		$ret->media = $media;

		return $ret;
	}

}