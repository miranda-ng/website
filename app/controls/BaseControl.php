<?php
namespace Components;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

abstract class BaseControl extends Control
{
	/** @var MyTexy */
	public $texy;

	/** @var ITranslator */
	public $translator;

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL, ITranslator $translator, \MyTexy $texy) {
		$this->translator = $translator;
		$this->texy = $texy;
		parent::__construct($parent, $name);
	}

	public function templatePrepareFilters($template) {
		$template->registerFilter($latte = new \Nette\Latte\Engine);
		\Macros::setupMacros($latte->compiler);
	}

	protected function createTemplate($class = NULL) {
		$template = parent::createTemplate($class);
		$template->setTranslator($this->translator);
		return \Macros::setupTemplate($template, $this->texy);
	}

	public function render() {
		$template = $this->template;

		$view = "default"; // TODO: edit for different renderXy methods?

		$name = $this->getReflection()->shortName;

		$dir = dirname($this->getReflection()->getFileName());

		$paths = array(
			"$dir/$name.latte",
			"$dir/$view.latte",
		);

		foreach($paths as $path) {
			if (is_file($path)) {
				$template->setFile($path);
				break;
			}
		}

		$template->render();
	}

}
