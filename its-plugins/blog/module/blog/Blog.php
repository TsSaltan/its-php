<?php
namespace tsframe\module\blog;

use tsframe\exception\PostNotFoundException;
use tsframe\module\blog\Category;
use tsframe\module\database\Database;

class Blog {
	public static function createPost(string $title, string $content, int $authorId, int $type, ?string $alias = null): Post {
		if(strlen($alias) == 0){
			$alias = self::generateAlias($title);
		} else {
			$alias = self::generateAlias($alias);
		}

		$alias = self::getFreeAlias($alias);

		$postId = Database::prepare('INSERT INTO `blog-posts` (`alias`, `title`, `content`, `author_id`, `type`, `create_time`, `update_time`) VALUES (:alias, :title, :content, :author_id, :type, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)')
				->bind('alias', $alias)
				->bind('title', $title)
				->bind('content', $content)
				->bind('author_id', $authorId)
				->bind('type', $type)
			->exec()
			->lastInsertId();

		if($postId == 0){
			throw new BlogException('Cannot create blog post', 0, [
				'alias' => $alias,
				'title' => $title,
				'content' => $content,
				'author_id' => $authorId,
				'type' => $type,
			]);
		}

		return self::getPostById($postId);
	}

	public static function getFreeAlias(string $alias, string $type = 'post'){
		$originalAlias = $alias;
		$ai = 0;
		while(self::isAliasExists($alias, $type)){
			$alias = $originalAlias . '-' . ++$ai;
		}

		return $alias;
	}

	public static function generateAlias(string $title){
		$string = mb_ereg_replace('([^0-9a-zа-яё\-]{1})', '-', mb_strtolower($title));
		$len = 0;
		while($len != strlen($string)){
			$len = strlen($string);
			$string = str_replace('--', '-', $string);
		}
		return trim($string, '-');
	}

	public static function getPostById(int $id): Post {
		$post = Database::prepare('SELECT *, UNIX_TIMESTAMP(`create_time`) as \'create_ts\', UNIX_TIMESTAMP(`update_time`) as \'update_ts\' FROM `blog-posts` WHERE `id` = :id')
						->bind('id', $id)
						->exec()
					->fetch();

		if(!isset($post[0])){
			throw new PostNotFoundException('Post (id=' . $id . ') not found');
		}		

		return new Post($post[0]['id'], $post[0]['alias'], $post[0]['title'], $post[0]['content'], $post[0]['create_ts'], $post[0]['update_ts'], $post[0]['author_id'], $post[0]['type']);
	}

	public static function getPostByAlias(string $alias): Post {
		$post = Database::prepare('SELECT *, UNIX_TIMESTAMP(`create_time`) as \'create_ts\', UNIX_TIMESTAMP(`update_time`) as \'update_ts\' FROM `blog-posts` WHERE `alias` = :alias')
						->bind('alias', $alias)
						->exec()
					->fetch();

		if(!isset($post[0])){
			throw new PostNotFoundException('Post (alias=' . $alias . ') not found');
		}		

		return new Post($post[0]['id'], $post[0]['alias'], $post[0]['title'], $post[0]['content'], $post[0]['create_ts'], $post[0]['update_ts'], $post[0]['author_id'], $post[0]['type']);
	}

	public static function getPostsNum(): int {
		$q = Database::exec('SELECT COUNT(*) as c FROM `blog-posts`')->fetch();
		return $q[0]['c'] ?? 0;
	}

	public static function getPosts(int $offset = 0, int $limit = 10): array {
		$posts = Database::exec('SELECT *, UNIX_TIMESTAMP(`create_time`) as \'create_ts\', UNIX_TIMESTAMP(`update_time`) as \'update_ts\' FROM `blog-posts` ORDER BY `create_time` DESC LIMIT ' . $offset . ',' . $limit)->fetch();
		$return = [];
		foreach($posts as $post){
			$return[] = new Post($post['id'], $post['alias'], $post['title'], $post['content'], $post['create_ts'], $post['update_ts'], $post['author_id'], $post['type']);
		}

		return $return;
	}

	public static function isAliasExists(string $alias, string $type = 'post'): bool {
		if($type == 'post'){
			$q = Database::exec('SELECT COUNT(*) as c FROM `blog-posts` WHERE `alias` = :alias', ['alias' => $alias])->fetch();
		}
		elseif($type == 'category'){
			$q = Database::exec('SELECT COUNT(*) as c FROM `blog-categories` WHERE `alias` = :alias', ['alias' => $alias])->fetch();
		} else {
			return false;
		}
		return ($q[0]['c'] ?? 0) > 0;
	}

	public static function getPostsInCategory(array $categories){
		$catList = [];
		foreach($categories as $cat){
			if($cat instanceof Category){
				$catList[] = $cat->getId();
			} else {
				$catList[] = (int) $cat;
			}
		}
		$count = sizeof($catList);

		$p = Database::exec(
			'SELECT *, UNIX_TIMESTAMP(`create_time`) as \'create_ts\', UNIX_TIMESTAMP(`update_time`) as \'update_ts\' 
			 FROM `blog-posts` p 
			 LEFT JOIN `blog-post-to-category` pc ON p.`id` = pc.`post-id` AND pc.`category-id` IN ('. implode(', ', $catList) .') 
			 GROUP BY p.id 
			 HAVING COUNT(*) = :count', 
			['count' => $count]
		)->fetch();	

		$return = [];
		foreach($p as $post){
			$return[] = new Post($post['id'], $post['alias'], $post['title'], $post['content'], $post['create_ts'], $post['update_ts'], $post['author_id'], $post['type']);
		}

		return $return;
	}
}