<?php
namespace tsframe\controller;

use tsframe\exception\AccessException;
use tsframe\Http;
use tsframe\module\Meta;
use tsframe\module\user\User;
use tsframe\module\user\SingleUser;
use tsframe\module\user\UserAccess;
use tsframe\module\user\SocialLogin;
use tsframe\module\io\Input;

/**
 * @route GET|POST /dashboard/social-login
 */
class SocialLoginController extends AbstractController{
	public function response(){
		$currentUser = User::current();
		$data = Input::post(false)
						->name('token')
						  ->required()
					  	  ->minLength(1)
						->assert();

		$login = new SocialLogin($data['token']);
		
		if(!$currentUser->isAuthorized()){
			$user = $login->getUser();
			$user->createSession();
			Http::redirect(Http::makeURI('/dashboard/'));
		} else {
			try{
				$login->saveUserMeta($currentUser);
				Http::redirect(Http::makeURI('/dashboard/user/me/edit', ['social' => 'success'], 'social'));
			} catch(AccessException $e){
				Http::redirect(Http::makeURI('/dashboard/user/me/edit', ['social' => 'fail'], 'social'));
			}
		}
	}
}