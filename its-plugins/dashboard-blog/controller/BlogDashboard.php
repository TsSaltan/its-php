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
 * @route GET  /dashboard/[blog:action]
 * @route GET  /dashboard/[blog-post:action]/[:post]
 */ 
class BlogDashboard extends UserDashboard {
	public function __construct(){
		$this->setActionPrefix(null);
	}

	public function getBlog(){
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

	public function getBlogPost(){
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
}