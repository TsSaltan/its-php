<?php
namespace tsframe\module\blog;

use tsframe\module\database\Database;

class Blog {
	public static function createPost(string $alias, string $title, string $content, int $authorId, int $type): Post {
		if(self::isPostAliasExists($alias)){
			$alias = uniqid($alias);
		}

		$postId = Database::prepare('INSERT INTO `blog-posts` (`alias`, `title`, `content`, `author_id`, `type`) VALUES (:alias, :title, :content, :author_id, :type)')
				->bind('alias', $alias)
				->bind('title', $title)
				->bind('content', $content)
				->bind('author_id', $authorId)
				->bind('type', $type)
			->exec()
			->lastInsertId();

		return self::getPostById($postId);
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

	public static function getPosts(): array {

	}

	public static function isPostAliasExists(string $alias): bool {
		$q = Database::exec('SELECT COUNT(*) as c FROM `blog-posts` WHERE `alias` = :alias')->fetch();
		return ($q[0]['c'] ?? 0) > 0;
	}
}