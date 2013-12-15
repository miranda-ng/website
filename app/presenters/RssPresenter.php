<?php

class RssPresenter extends \BasePresenter
{	

	public function renderNews()
	{		
		$limit = 15;
		
		$data = $this->context->database->table('news')->order("date DESC")->limit($limit);
		$this->template->data = $data;
		$this->setView("default");
	}
	
}