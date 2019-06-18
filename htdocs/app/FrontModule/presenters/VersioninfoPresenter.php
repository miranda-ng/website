<?php

namespace FrontModule;

use Nette\Application\UI\Form;
use Nette\Http\Response;
use Nette\Utils\Strings;

final class VersioninfoPresenter extends BasePresenter
{
	/** @var \Models\PagesModel @inject */
	public $pagesModel;
	
	/** @persistent */
	public $sort_core = "count";
	
	/** @persistent */
	public $sort_lang = "count";
	
	/** @persistent */
	public $sort_plugins = "count";
	
	/** @persistent */
	public $filter;

	const PASSWORD_MAX_LENGTH = 4096;

	function renderDefault() {
		$this->template->page = $this->pagesModel->get(4);
	}

	public function renderDetail($id)
	{
		$item = $this->context->database->table("users")->where("login", $id)/*->where("vi IS NOT NULL")*/->fetch();
		if (!$item) {
			//$this->error($this->translator->translate("Version info was not found."));
			$this->flashMessage("User with this login was not found.", "error");
			$this->redirect('default');
			return;
		}
		$this->template->item = $item;
	}

	public function renderStats()
	{
		$users = $this->context->database->table('users');

		$count = 0;
		$viCount = 0;
		$pluginsCount = 0;
		$plugins = array();
		$cores = array();
		$langs = array();
		$maxPlugins = false;
		$minPlugins = false;

		foreach ($users as $user) {
			$count++;

			if (!$user->vi) {
				continue;
			}

			if ($this->filter && mb_stripos($user->vi, $this->filter) === false) {
				continue;
			}
			
			$viCount++;

			$parsingPlugins = false;
			foreach(preg_split("/((\r?\n)|(\r\n?))/", $user->vi) as $line) {
				$line = trim($line);

				if ($parsingPlugins) {
					if (!$line) {
						break;
					}

					if (preg_match("/([^\(\)]+)\.dll .*? - (.*)/", $line, $matches)) {
						$dllName = strtolower(trim($matches[1], "¤  \r\n\t")) . ".dll";
						$pluginName = $matches[2];
						
						if (preg_match("/^(.*?)(\|(ANSI|Unicode aware)\|.*?)?$/", $pluginName, $nameMatches)) {
							$pluginName = $nameMatches[1];
						}
						$id = strtolower(trim($pluginName));
						
						if (isset($plugins[$id])) {
							$plugins[$id]->count++;
						} else {
							$plugins[$id] = (object)array();
							$plugins[$id]->count = 1;
							$plugins[$id]->name = $pluginName;
							$plugins[$id]->dllName = $dllName;
						}
						//$pluginsCount++;
					}
				} else {
					//if (preg_match("/^Miranda NG Version: ([^\[]+)/", $line, $matches)) {
					if (preg_match("/^Miranda NG Version: (.+) build(.*)/", $line, $matches)) {
						$core = $raw = strtolower(trim($matches[1]));
						if (strpos($matches[2], "x64") !== false)
							$core .= " x64";

						if (isset($cores[$core])) {
							$cores[$core]->count++;
						} else {
							$cores[$core] = (object)array();
							$cores[$core]->count = 1;
							$cores[$core]->version = $core;
							$cores[$core]->raw = $raw;
						}
					}
					
					if (preg_match("/^Language pack: (.*)/", $line, $matches)) {
						$lang = strtolower(trim($matches[1]));

						if (isset($langs[$lang])) {
							$langs[$lang]->count++;
						} else {
							$langs[$lang] = (object)array();
							$langs[$lang]->count = 1;
							$langs[$lang]->name = $lang;
						}
					}

					if (preg_match("/^Active Plugins \(([0-9]+)\)/", $line, $matches)) {
						$parsingPlugins = true;
						$pluginsCount += $matches[1];

						if ($minPlugins === false || $minPlugins > $matches[1])
							$minPlugins = $matches[1];

						if ($maxPlugins === false || $maxPlugins < $matches[1])
							$maxPlugins = $matches[1];

						continue;
					}
				}
			}
		}

		$countSort = function($a, $b) {
			$l = $a->count;
			$r = $b->count;

			if ($l == $r)
				return 0;
			elseif ($l < $r)
				return 1;
			else
				return -1;
		};

		if ($this->sort_core == "name")
			ksort($cores);
		else
			usort($cores, $countSort);
		
		if ($this->sort_lang == "name")
			ksort($langs);
		else
			usort($langs, $countSort);
		
		if ($this->sort_plugins == "name")
			ksort($plugins);
		else
			usort($plugins, $countSort);
		
		$this->template->filter = $this->filter;
		
		$this->template->usersCount = $count;
		$this->template->viCount = $viCount;
		$this->template->cores = $cores;
		$this->template->languages = $langs;
		$this->template->plugins = $plugins;
		$this->template->pluginsCount = $pluginsCount;

		$this->template->minPlugins = $minPlugins;
		$this->template->maxPlugins = $maxPlugins;
	}

	public function createComponentShowForm($name) {
		$form = new Form($this, $name);

		$form->setTranslator($this->translator);

		$form->addText("login", "Login:")
				->setRequired(); // TODO: As long as in DB are old weird logins, we can't check rules here
				//->addRule(Form::MIN_LENGTH, NULL, 3)
				//->addRule(Form::PATTERN, "Login can contain only ascii characters (a-Z) and numbers (0-9).", "^[a-zA-Z0-9]+$");

		$form->addSubmit('submit', 'Show');

		$form->onSuccess[] = $this->showFormSubmitted;
	}

	public function showFormSubmitted($form) {
		$values = $form->getValues();
		$this->redirect('detail', array("id" => $values["login"]));
	}

	public function createComponentRegisterForm($name) {
		$form = new Form($this, $name);

		$form->setTranslator($this->translator);

		$form->addProtection('Timeout for security token. Submit form again.');

		$form->addText("login", "Login:")
				->setOption("description", "Use only ascii characters (a-Z) and numbers (0-9).")
				->setRequired()
				->addRule(Form::MIN_LENGTH, NULL, 3)
				->addRule(Form::PATTERN, "Login can contain only ascii characters (a-Z) and numbers (0-9).", "^[a-zA-Z0-9]+$");

		$form->addText("email", "E-mail:")
				->setRequired()
				->addRule(Form::EMAIL);

		$form->addPassword("password", "Password:")
				->setRequired()
				->addRule(Form::MIN_LENGTH, NULL, 6);

		$form->addPassword("password2", "Password (again):")
				->setRequired();

		$form->addSubmit('submit', 'Register');

		$form->onSuccess[] = $this->registerFormSubmitted;
	}

	public function registerFormSubmitted($form) {
		$values = $form->getValues();

		if ($values['password'] != $values['password2']) {
			$this->flashMessage("Passwords must match.", "error");
			return;
		}

		if ($this->context->database->table('users')->where('login', $values['login'])->fetch() != NULL) {
			$this->flashMessage("User with this login already exists.", "error");
			return;
		}

		$res = $this->context->database->table('users')->insert(array(
			"login" => $values["login"],
			"password" => md5($values["password"]),//$this->hashPassword($values["password"]),
			"email" => $values["email"],
		));

		if ($res) {
			$this->flashMessage("You have been registered successfully.", "success");
		} else {
			$this->flashMessage("There was an error when registering you.", "error");
		}

		$this->redirect("default");
	}

	public function actionUpload($login, $pass) {
		$user = $this->context->database->table('users')->where('login', $login)->where('password', $pass)->fetch();
		if ($user == NULL) {
			$this->getHttpResponse()->setCode(401);
			$this->sendResponse(new \Nette\Application\Responses\TextResponse("Fuck you"));
			$this->terminate();
		}

		$postdata = file_get_contents("php://input");

		$user->update(array(
			'vi' => $postdata,
			'uploaded' => new \Nette\DateTime(),
		));

		echo "OK";
		$this->terminate();
	}



		/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public static function hashPassword($password, $options = NULL)
	{
		if ($password === Strings::upper($password)) { // perhaps caps lock is on
			$password = Strings::lower($password);
		}
		$password = substr($password, 0, self::PASSWORD_MAX_LENGTH);
		$options = $options ?: implode('$', array(
			'algo' => PHP_VERSION_ID < 50307 ? '$2a' : '$2y', // blowfish
			'cost' => '07',
			'salt' => Strings::random(22),
		));
		return crypt($password, $options);
	}


	/**
	 * Verifies that a password matches a hash.
	 * @return bool
	 */
	public static function verifyPassword($password, $hash)
	{
		return self::hashPassword($password, $hash) === $hash
			|| (PHP_VERSION_ID >= 50307 && substr($hash, 0, 3) === '$2a' && self::hashPassword($password, $tmp = '$2x' . substr($hash, 3)) === $tmp);
	}


}
