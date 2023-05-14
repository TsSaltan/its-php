<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\Plugins;
use tsframe\exception\UserException;
use tsframe\module\Paginator;
use tsframe\module\SitemapGenerator;
use tsframe\module\SitemapItem;
use tsframe\module\blog\Blog;
use tsframe\module\blog\Category;
use tsframe\module\io\Input;
use tsframe\module\io\Output;
use tsframe\module\user\SingleUser;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;

/**
 * @route GET 		/dashboard/blog/[posts|categories:action]
 * @route GET  		/dashboard/blog/[post:action]/[i:post]
 * @route GET|POST 	/dashboard/blog/post/[new:action]
 * @route POST  	/dashboard/blog/post/[i:post]/[save|delete:action]
 * @route GET|POST	/dashboard/blog/[category:action]/[i:category]
 * @route POST  	/dashboard/blog/[delete-category:action]/[i:category]
 * @route POST  	/dashboard/blog/[create-category:action]
 */ 
class BlogDashboard extends UserDashboard {
	protected static $linkMaker;
	public static function linkMaker(?callable $func){
		self::$linkMaker = $func;
	}

	public function __construct(){
		$this->setActionPrefix(null);
	}

	public function getCategories(){
		UserAccess::assertCurrentUser('blog');
		$this->vars['title'] = __('menu/blog-categories');
		$this->viewCategories(-1);
	}

	protected function viewCategories(int $parentId){
		$count = Category::getNum($parentId);
		$pages = new Paginator([], 10);
		$pages->setDataSize($count);

		$pages->setTotalDataCallback(function($offset, $limit) use ($parentId){
			return Category::getList($offset, $limit, $parentId);
		});

		$this->vars['categories'] = $pages;
		$this->vars['catsNum'] = $count;

		if(isset($_GET['action']) && in_array($_GET['action'], ['create', 'edit', 'delete']) && isset($_GET['result']) && in_array($_GET['result'], ['success', 'error'])){
			$type = $_GET['result'] == 'error' ? 'danger' : 'success';
			$this->vars['alert'][$type] = __('blog-category-result-' . $_GET['action'] . '-' . $_GET['result']);
		}
	}

	public function getPosts(){
		UserAccess::assertCurrentUser('blog');
		$this->vars['title'] = __('menu/blog-writes');
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
		$this->vars['catsStruct'] = Category::getAll(true);
		$this->vars['postCategories'] = array_keys($post->getCategories()); // contains only ids of category
		$this->vars['postLink'] = false;

		if(is_callable(self::$linkMaker)){
			$this->vars['postLink'] = call_user_func(self::$linkMaker, $post);

			if(Plugins::isEnabled('sitemap')){
				$item = new SitemapItem($this->vars['postLink'], $post->getUpdateTime(SitemapItem::DATE_FORMAT), 'monthly', 0.8);
				$item->addToGenerator();
			}
		}
	}

	public function getNew(){
		UserAccess::assertCurrentUser('blog');
		$this->vars['title'] = __('menu/new-blog-post');
		$this->vars['catsStruct'] = Category::getAll(true);
	}

	public function postNew(){
		UserAccess::assertCurrentUser('blog');
		
		$data = Input::post()
			->name('title')->string()->required()
			->name('alias')->string()->required()
			->name('content')->string()->required()
			->name('type')->values(['0', '1'])->required()
			->name('categories')->array()->optional()
		->assert();

		$post = Blog::createPost($data['title'], $data['content'], User::current()->get('id'), $data['type'], $data['alias']);

		$cats = $data['categories'];
		if(is_array($cats) && sizeof($cats) > 0){
			$post->setCategories($cats);
		}

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
			->name('categories')->array()->optional()
		->assert();

		$res = $post->update($data['alias'], $data['title'], $data['content'], User::current()->get('id'), $data['type']);
		$cats = $data['categories'];
		if(is_array($cats) && sizeof($cats) > 0){
			$post->setCategories($cats);
		}
		return Http::redirectURI('/dashboard/blog/post/' . $postId, ['from' => 'edit', 'result' => $res ? 'success' : 'fail']);
	}

	public function postDelete(){
		UserAccess::assertCurrentUser('blog');
		$postId = $this->params['post'];
		$post = Blog::getPostById($postId);
		$post->delete();

		if(is_callable(self::$linkMaker)){
			$this->vars['postLink'] = call_user_func(self::$linkMaker, $post);

			if(Plugins::isEnabled('sitemap')){
				SitemapGenerator::removeUrl($this->vars['postLink']);
			}
		}
		
		return Http::redirectURI('/dashboard/blog/posts', ['from' => 'delete']);
	}

	public function postCreateCategory(){
		UserAccess::assertCurrentUser('blog');

		try {
			$data = Input::post()
				->name('title')->string()->notEmpty()->required()
				->name('parent-id')->numeric()->notEmpty()->required()
				->name('alias')->string()->optional()
			->assert();

			$category = Category::create($data['title'], $data['alias']);
			if($data['parent-id'] != -1){
				$parent = Category::getById($data['parent-id']);
				$category->setParent($parent);
			}
		} catch (\Exception $e){
			var_dump($e->getMessage());
			die;
			return Http::redirectURI('/dashboard/blog/categories', ['action' => 'create', 'result' => 'error']);
		}

		if(isset($parent) && is_object($parent)){
			return Http::redirectURI('/dashboard/blog/category/' . $parent->getId(), ['action' => 'create', 'result' => 'success'], 'category' . $category->getId());
		} else {
			return Http::redirectURI('/dashboard/blog/categories', ['action' => 'create', 'result' => 'success'], 'category' . $category->getId());
		}
	}

	public function getCategory(){
		UserAccess::assertCurrentUser('blog');

		try {
			$cid = $this->params['category'] ?? null;
			$category = Category::getById($cid);
		} catch (\Exception $e){
			return Http::redirectURI('/dashboard/blog/categories', ['action' => 'edit', 'result' => 'error', 'requestId' => $cid]);
		}

		$this->vars['category'] = $category;
		$this->viewCategories($cid);
		$this->vars['allCategories'] = Category::getAll();
	}

	public function postCategory(){
		UserAccess::assertCurrentUser('blog');

		try {
			$cid = $this->params['category'] ?? null;
			$category = Category::getById($cid);

			$data = Input::post()
				->name('title')->string()->notEmpty()->required()
				->name('parent-id')->numeric()->notEmpty()->required()
				->name('alias')->string()->optional()
			->assert();

			$category->update($data['title'], $data['alias']);

			if($data['parent-id'] == -1){
				$category->setParent(null);
			} else {
				$parent = Category::getById($data['parent-id']); 
				$category->setParent($parent);
			}

		} catch (\Exception $e){
			return Http::redirectURI('/dashboard/blog/categories', ['action' => 'edit', 'result' => 'error', 'requestId' => $cid]);
		}
		
		return Http::redirectURI('/dashboard/blog/categories', ['action' => 'edit', 'result' => 'success', 'requestId' => $cid]);
	}

	public function postDeleteCategory(){
		UserAccess::assertCurrentUser('blog');
		try {
			$cid = $this->params['category'] ?? null;
			$category = Category::getById($cid);
			$category->delete();
		} catch (\Exception $e){
			return Http::redirectURI('/dashboard/blog/categories', ['action' => 'delete', 'result' => 'error', 'requestId' => $cid]);
		}

		return Http::redirectURI('/dashboard/blog/categories', ['action' => 'delete', 'result' => 'success']);
	}
}