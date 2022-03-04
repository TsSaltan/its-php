<?php
namespace tsframe\module\menu;

class MenuItem {
	public static $menuNum = 0; 

	protected $id;
	protected $title;
	protected $data = [];
	protected $children = [];

	public function __construct(?string $title = null, array $data = [], ?string $id = null){
		$this->setTitle($title);
		$this->data = $data;

		if(!is_null($id)){
			$this->id = $id;
		} else {
			$this->id = 'menu-' . (++self::$menuNum);
		}
	}

	public function getId(){
		return $this->id;
	}

	public function add(MenuItem $menu, ?int $index = null): MenuItem {
		$this->children[$menu->getId()] = [
			'menu' => $menu,
			'index' => is_null($index) ? sizeof($this->children): $index
		];
		return $this;
	}

    public function remove($menuId): MenuItem {
    	unset($this->children[$menuId]);
    	return $this;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): MenuItem {
        $this->title = $title;
        return $this;
    }

    public function getData(string $item){
        return $this->data[$item] ?? null;
    }

    public function setData(string $key, $value): MenuItem {
        $this->data[$key] = $value;
        return $this;
    }

    public function getChildren(): array {
    	usort($this->children, function($a, $b){
    		return $a['index'] > $b['index'] ? 1 : -1;
    	});

        return array_column($this->children, 'menu');
    }

    public function hasChildren(): bool {
        return sizeof($this->children) > 0;
    }
}