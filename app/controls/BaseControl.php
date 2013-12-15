<?php
namespace Components;

use Nette\Application\UI\Control;

abstract class BaseControl extends Control
{

	public function templatePrepareFilters($template) {
		$template->registerFilter($latte = new \Nette\Latte\Engine);
		\Macros::setupMacros($latte->compiler);
	}

	protected function createTemplate($class = NULL) {
		$template = parent::createTemplate($class);
		return \Macros::setupTemplate($template, $this->presenter->context->parameters["wwwDir"]);
	}

	public function render() {
		$template = $this->template;

		$view = "default"; // TODO: upravit pro různé renderNeco metody?

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
