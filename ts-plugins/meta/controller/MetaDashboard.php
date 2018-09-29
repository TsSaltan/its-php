<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\Log;
use tsframe\module\io\Output;
use tsframe\module\io\Input;
use tsframe\module\Paginator;
use tsframe\module\Meta;

/**
 * @route GET /dashboard/[meta:action]
 * @route POST /ajax/[meta:action]
 */ 
class MetaDashboard extends UserDashboard {

	protected $actionPrefix = '';

	public function getMeta(){
		UserAccess::assertCurrentUser('meta');


		$this->vars['title'] = 'МЕТА - реестр данных';
		$filter = $_GET['filter'] ?? null;
		$pages = new Paginator(Meta::getParentList($filter));
		$pages->setDataCallback(function($data){
			$m = new Meta($data);
			$metaData = $m->getData();
			return ['parent' => $data, 'data' => Output::of($metaData)->quotes()->getData()];
		});

		$this->vars['metaData'] = $pages;
		$this->vars['filter'] = Output::of($filter)->quotes()->getData();

	}

	public function postMeta(){
		UserAccess::assertCurrentUser('meta');
		Input::post()
			->name('parent')
				->required()
				->minLength(1)
			->name('key')
				->required()
				->minLength(1)
			->name('value')
				->optional()
			->assert();

		$m = new Meta($_POST['parent']);
		$m->set($_POST['key'], $_POST['value']);

		Http::sendBody(json_encode("OK"), Http::CODE_OK, Http::TYPE_JSON);
		die;
	}
}