<?php
namespace tsframe\module\blog;

use tsframe\module\database\Database;
use tsframe\module\io\Output;

class Post {
	const TYPE_TEMPLATE = 0;
	const TYPE_POST = 1;

	protected $id;
	protected $alias;
	protected $title;
	protected $content;
	protected $createTime;
	protected $updateTime;
	protected $authorId;
	protected $type;

	public function __construct(int $id, string $alias, string $title, string $content, int $createTime, int $updateTime, int $authorId, int $type){
		$this->id = $id;
		$this->alias = $alias;
		$this->title = $title;
		$this->content = $content;
		$this->createTime = $createTime;
		$this->updateTime = $updateTime;
		$this->authorId = $authorId;
		$this->type = $type;
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

	public function getContent(): string {
		return $this->content;
	}

	public function getAuthorId(): int {
		return $this->authorId;
	}

	public function getType(): int {
		return $this->type;
	}

	public function getCreateTime(string $format = 'Y-m-d H:i:s'){
		return date($format, $this->createTime);
	}

	public function getUpdateTime(string $format = 'Y-m-d H:i:s'){
		return date($format, $this->updateTime);
	}

	public function update(string $alias, string $title, string $content, int $authorId, int $type): bool {
		$alias = Blog::generateAlias($alias);
		
		if( Database::exec(
			'UPDATE `blog-posts` SET `alias` = :alias, `title` = :title, `content` = :content, `author_id` = :authorId,  `type` = :type, `update_time` = CURRENT_TIMESTAMP WHERE `id` = :id', 
			[
				'id' => $this->getId(),
				'alias' => $alias,
				'title' => $title,
				'content' => $content,
				'authorId' => $authorId,
				'type' => $type
			]
		)->affectedRows() > 0 ){
			$this->updateTs();
			return true;
		}

		return false;
	}
}