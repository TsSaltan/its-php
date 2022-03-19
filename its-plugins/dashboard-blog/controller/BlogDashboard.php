<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\exception\UserException;
use tsframe\module\Paginator;
use tsframe\module\blog\Blog;
use tsframe\module\io\Input;
use tsframe\module\io\Output;
use tsframe\module\user\SingleUser;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;

/**
 * @route GET 		/dashboard/blog/[posts:action]
 * @route GET  		/dashboard/blog/[post:action]/[i:post]
 * @route GET|POST 	/dashboard/blog/post/[new:action]
 * @route POST  	/dashboard/blog/post/[i:post]/[save|delete:action]
 */ 
class BlogDashboard extends UserDashboard {
	public function __construct(){
		$this->setActionPrefix(null);
	}

	public function getPosts(){
		UserAccess::assertCurrentUser('blog');

		$this->vars['title'] = __('menu/blog-writes');
		
		// Count and pages
		$count = Blog::getPostsNum();
		$pages = new Paginator([], 10);
		$pages->setDataSize($count);

		$pages->setTotalDataCallback(function($offset, $limit){
			return Blog::getPosts($offset, $limit);
		});

		$this->vars['posts'] = $pages;
		$this->vars['postsNum'] = $count;
	}

	public function getPost(){
		UserAccess::assertCurrentUser('blog');

		$postId = $this->params['post'];
		$post = Blog::getPostById($postId);
		
		$this->vars['post'] = $post;
		try {
			$this->vars['author'] = User::getById($post->getAuthorId());
		} catch (UserException $e){
			$this->vars['author'] = SingleUser::unauthorized();
		}
		$this->vars['title'] = __('menu/edit-blog-post');
	}

	public function getNew(){
		UserAccess::assertCurrentUser('blog');
		$this->vars['title'] = __('menu/new-blog-post');
	}

	public function postNew(){
		UserAccess::assertCurrentUser('blog');
		
		$data = Input::post()
			->name('title')->string()->required()
			->name('alias')->string()->required()
			->name('content')->string()->required()
			->name('type')->values(['0', '1'])->required()
		->assert();

		$post = Blog::createPost($data['title'], $data['content'], User::current()->get('id'), $data['type'], $data['alias']);
		return Http::redirectURI('/dashboard/blog/post/' . $post->getId(), ['from' => 'post']);
	}

	public function postSave(){
		UserAccess::assertCurrentUser('blog');
		$postId = $this->params['post'];
		$post = Blog::getPostById($postId);

		$data = Input::post()
			->name('title')->string()->required()
			->name('alias')->string()->required()
			->name('content')->string()->required()
			->name('type')->values(['0', '1'])->required()
		->assert();

		$res = $post->update($data['alias'], $data['title'], $data['content'], User::current()->get('id'), $data['type']);
		return Http::redirectURI('/dashboard/blog/post/' . $postId, ['from' => 'edit', 'result' => $res ? 'success' : 'fail']);
	}

	public function postDelete(){
		UserAccess::assertCurrentUser('blog');
		$postId = $this->params['post'];
		$post = Blog::getPostById($postId);
		$post->delete();
		
		return Http::redirectURI('/dashboard/blog/posts', ['from' => 'delete']);
	}
}