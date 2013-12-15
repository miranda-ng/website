<?php

namespace AdminModule;

use Nette\Security\User;

abstract class SecuredPresenter extends BasePresenter
{
	public function startup() {
		parent::startup();
		
		$user = $this->user;
		if (!$user->isLoggedIn()) {
			if ($user->getLogoutReason() === User::INACTIVITY) {
				$this->flashMessage('You was logged out due to inactivity.', 'warning');
			}
			$backlink = $this->storeRequest();
			$this->redirect(':Admin:Sign:', array('backlink' => $backlink));
		} 
	
	}
	
	public function beforeRender()
	{
		parent::beforeRender();
		
		if (isset($this->title)) {
			$this->template->title = $this->title;
		}
	}	
	
}
