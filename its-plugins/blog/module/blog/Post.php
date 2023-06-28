<?php
namespace tsframe\module\blog;

use tsframe\Hook;
use tsframe\module\blog\Category;
use tsframe\module\database\Database;
use tsframe\module\io\Output;

class Post {
	const TYPE_DRAFT = 0;
	const TYPE_PRODUCTION = 1;

	protected $id;
	protected $alias;
	protected $title;
	protected $content;
	protected $createTime;
	protected $updateTime;
	protected $authorId;
	protected $type;
	protected $categories = [];

	public function __construct(int $id, string $alias, string $title, string $content, ?int $createTime, ?int $updateTime, int $authorId, int $type){
		$this->id = $id;
		$this->alias = $alias;
		$this->title = $title;
		$this->content = $content;
		$this->createTime = is_null($createTime) ? time() : $createTime;
		$this->updateTime = is_null($updateTime) ? time() : $updateTime;
		$this->authorId = $authorId;
		$this->type = $type;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getAlias(): string {
		return $this->alias;
	}

	public function getCategories(): array {
		if(sizeof($this->categories) == 0){
			$this->categories = Category::getPostCategories($this);
		}

		return $this->categories;
	}

	public function setCategories(array $categories){
		$this->categories = [];
		Category::setPostCategories($this, $categories);
	}

	public function getTitle(int $maxLength = 0): string {
		$title = Output::of($this->title)->specialChars()->quotes()->getData();
		if($maxLength > 0 && strlen($title) > $maxLength){
			return mb_substr($title, 0, $maxLength) . ' ...';
		}

		return $title;
	}

	public function getContent(bool $processHtml): string {
		if($processHtml){
			$content = nl2br($this->content);

			if(preg_match_all('#<!--\s*hook:([^\s]+?)\s*([^\s]+?)?\s*-->#Ui', $content, $find)){
				foreach($find[1] as $i => $filterName){
					$replacingFrom = $find[0][$i];
					$replacingTo = null;
					parse_str($find[2][$i], $params);
					Hook::call('blog.post.' . $filterName, [$this, $params], function(?string $result, ?string $output) use (&$replacingTo){
						if(strlen($result) > 0)	$replacingTo = $result;
						if(strlen($output) > 0)	$replacingTo = $output;
					}, null, false, false);
					$content = str_replace($replacingFrom, $replacingTo, $content);
				}
			}

			return $content;
		}

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

	public function update(?string $alias, string $title, string $content, int $authorId, int $type): bool {
		$alias = strlen($alias) == 0 ? Blog::generateAlias($title) : Blog::generateAlias($alias);
		
		if($alias != $this->alias){
			$alias = Blog::getFreeAlias($alias);
		}

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
			$this->alias = $alias;
			$this->title = $title;
			$this->content = $content;
			$this->authorId = $authorId;
			$this->type = $type;
			$this->updateTime = time();

			return true;
		}

		return false;
	}

	public function isDraft(): bool {
		return $this->getType() == self::TYPE_DRAFT;
	}

	public function isProduction(): bool {
		return $this->getType() == self::TYPE_PRODUCTION;
	}

	public function delete(): bool {
		return Database::exec(
			'DELETE FROM `blog-posts` WHERE `id` = :id', ['id' => $this->getId()]
		)->affectedRows() > 0;
	}
}