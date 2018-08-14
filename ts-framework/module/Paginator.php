<?php
namespace tsframe\module;

use tsframe\utils\io\Filter;

class Paginator{
	/**
	 * Данные для отображения
	 * @var array
	 */
	protected $data = [];

	/**
	 * Текущая страница
	 * @var int
	 */
	protected $page;

	/**
	 * Количество элементов на странице
	 * @var int
	 */
	protected $num;

	/**
	 * Номер последней страницы
	 * @var int
	 */
	protected $last;

	/**
	 * Отступ
	 * @var int
	 */
	protected $offset;

	public function __construct(array $data, int $num = 20){
		$this->last = ceil(sizeof($data) / $num);
		$this->page = min(max($_GET['page'] ?? 1, 1), $this->last);
		$this->num = $num;
		$this->data = $data;
		$this->offset = ($this->page-1) * $this->num;

	}	

	public function getData(): array {
		return array_slice($this->data, $this->offset, $this->num);
	}

	public function getPages(int $pagesNum = 5, bool $helpers = true): array {
		$pages = [];

		if($helpers){
			$pages[] = ['title' => '<<', 'url' => '?page=1', 'current' => $this->page == 1];
			$pages[] = ['title' => '<', 'url' => '?page=' . ($this->page-1), 'current' => $this->page == 1];
		}

		$start = $this->page-floor($pagesNum/2);
		$startIndex = max($start, 1);
		$end = $this->page+floor($pagesNum/2);
		$endIndex = min($end, $this->last);

		for($index = $startIndex - ($end-$endIndex); $index <= $endIndex + ($startIndex - $start); ++$index){
			if($index < 1 || $index > $this->last) continue;
			$pages[] = ['title' => $index, 'url' => '?page=' . $index, 'current' => $this->page == $index];
		}

		if($helpers){
			$pages[] = ['title' => '>', 'url' => '?page=' . ($this->page+1), 'current' => $this->page >= $this->last];
			$pages[] = ['title' => '>>', 'url' => '?page=' . $this->last, 'current' => $this->page >= $this->last];
		}

		return $pages;
	}
}