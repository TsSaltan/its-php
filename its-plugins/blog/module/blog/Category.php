<?php
namespace tsframe\module\blog;

use tsframe\Hook;
use tsframe\exception\CategoryNotFoundException;
use tsframe\module\database\Database;
use tsframe\module\io\Output;

class Category {
	public static function create(string $title, ?string $alias = null): Category {
		$alias = strlen($alias) == 0 ? Blog::generateAlias($title) : Blog::generateAlias($alias);
		$alias = Blog::getFreeAlias($alias, 'category');
		$id = Database::exec('INSERT INTO `blog-categories` (`title`, `alias`) VALUES (:title, :alias)', ['title' => $title, 'alias' => $alias])->lastInsertId();

		return self::getById($id);
	}

	public static function getNum(int $parentId = -1): int {
		$c = Database::exec('SELECT COUNT(*) as c FROM `blog-categories` WHERE `parent-id` = :pid', ['pid' => $parentId])->fetch();
		return $c[0]['c'] ?? -1;
	}

	public static function getList(int $offset, int $limit, int $parentId): array {
		$datas = Database::exec('SELECT * FROM `blog-categories` WHERE `parent-id` = :pid ORDER BY `title` ASC LIMIT ' . $offset . ',' . $limit, ['pid' => $parentId])->fetch();
		$cats = [];
		foreach($datas as $data){
			$cats[] = new self($data['id'], $data['parent-id'], $data['title'], $data['alias']);
		}

		return $cats;

	}

	public static function getById(int $id){
		$data = Database::exec(
			'SELECT * FROM `blog-categories` WHERE `id` = :id', ['id' => $id]
		)->fetch();

		if(!isset($data[0])){
			throw new CategoryNotFoundException('Category (id='.$id.') does not found');
		}

		return new self($data[0]['id'], $data[0]['parent-id'], $data[0]['title'], $data[0]['alias']);
	}

	public static function setPostCategories(array $categories, Post $post){
		Database::exec('DELETE FROM `blog-post-to-category` WHERE `post-id` = :pid', ['pid' => $post->getId()]);
		foreach($categories as $category){
			Database::exec('INSERT INTO `blog-post-to-category` (`post-id`, `category-id`) VALUES (:pid, :cid)', ['pid' => $post->getId(), 'cid' => $category->getId()]);
		}

	}

	public static function getPostCategories(int $postId): array {
		$categories = Database::exec(
			'SELECT * FROM `blog-categories` as bc
				LEFT JOIN `blog-post-to-category` as p2c ON p2c.`category-id` = bc.`id`
				WHERE p2c.`post-id` = :pid', ['pid' => $postId]
		)->fetch();

		if(sizeof($categories) == 0){
			return [];
		}

		$cats = [];
		foreach ($categories as $category){
			$cats[$category['category-id']] = new self($category['category-id'], $category['parent-id'], $category['title'], $category['alias']);
		}

		return array_values($cats);
	}

	protected $id;
	protected $parent;
	protected $title;
	protected $alias;

	public function __construct(int $id, int $parentId, string $title, string $alias){
		$this->id = $id;
		$this->alias = $alias;
		$this->title = $title;

		try {
			$this->parent = ($parentId >= 0) ? self::getById($parentId) : null;
		} catch (CategoryNotFoundException $e){
			$this->parent = null;
		}
	}

	public function getId(): int {
		return $this->id;
	}

	public function getAlias(): string {
		return $this->alias;
	}

	public function getTitle(): string {
		return Output::of($this->title)->specialChars()->quotes()->getData();
	}

	public function setParent(Category $parent): bool {
		if( Database::exec(
			'UPDATE `blog-categories` SET `parent-id` = :pid WHERE `id` = :id', 
			[
				'id' => $this->getId(),
				'pid' => $parent->getId(),
			]
		)->affectedRows() > 0 ){
			$this->parent = $parent;
			return true;
		}

		return false;
	}

	public function update(string $title, ?string $alias = null): bool {
		$alias = strlen($alias) == 0 ? Blog::generateAlias($title) : Blog::generateAlias($alias);
		
		if($alias != $this->alias){
			$alias = Blog::getFreeAlias($alias, 'category');
		}

		if( Database::exec(
			'UPDATE `blog-categories` SET `alias` = :alias, `title` = :title WHERE `id` = :id', 
			[
				'id' => $this->getId(),
				'alias' => $alias,
				'title' => $title
			]
		)->affectedRows() > 0 ){

			$this->alias = $alias;
			$this->title = $title;
			return true;
		}

		return false;
	}

	public function delete(): bool {
		Database::exec('DELETE FROM `blog-post-to-category` WHERE `category-id` = :id', ['id' => $this->getId()]);
		Database::exec('UPDATE `blog-categories` SET `parent-id` = -1 WHERE `parent-id` = :id', ['id' => $this->getId()]);
		return Database::exec('DELETE FROM `blog-categories` WHERE `id` = :id', ['id' => $this->getId()])->affectedRows() > 0;
	}
}