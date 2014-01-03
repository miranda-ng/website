<?php

namespace AdminModule;

use Nette\Application\UI,
	Nette\Security as NS;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{
	/** @persistent */
    public $backlink = '';

	public function beforeRender() {
		parent::beforeRender();

		if ($this->getUser()->isLoggedIn()) {
			$this->flashMessage($this->translator->translate('You are already logged in.'), 'success');
			$this->redirect(307, 'Home:');
		}
	}

	/**
	 * Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new UI\Form;
		$form->setTranslator($this->translator);

		$form->addText('login', 'Login:')
			->setRequired('Enter your login.');

		$form->addPassword('password', 'Password:')
			->setRequired('Enter your password.');

		$form->addCheckbox('remember', 'Remember login');

		$form->addSubmit('submit', 'Login');

		$form->onSuccess[] = $this->signInFormSubmitted;
		return $form;
	}

	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			if ($values->remember) {
				$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('+ 20 minutes', TRUE);
			}
			$this->getUser()->login($values->login, $values->password);

			$this->restoreRequest($this->backlink);
			$this->flashMessage($this->translator->translate('You have been succesfully logged in.'), 'success');
            $this->redirect(307, 'Home:');
		} catch (NS\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage($this->translator->translate('You have been logged out.'));
		$this->redirect(307, 'default');
	}

}
