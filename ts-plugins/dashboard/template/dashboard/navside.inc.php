<!-- Sidebar -->
<div id="sidebar" class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav navbar-collapse">
        <?php if($user->isAuthorized()):?>
            <?=$this->menu('dashboard-sidebar', 
                function(string $items, int $level){
                    if($level == 0){
                        return "<ul class=\"nav\" id=\"side-menu\">$items</ul>";    
                    }
                    
                    return "<ul class=\"nav nav-second-level\">$items</ul>";    
                }, 

                function($item, $sub) use ($user){
                    if(!$user->isAccess($item->getData('access'))) return;
                    $counter = intval($item->getData('counter'));
                    return '<li><a href="'. $item->getData('url') .'"><i class="fa fa-fw fa-'. $item->getData('fa') .'"></i> '. $item->getTitle() . ($counter > 0 ? ' <span class="counter btn-primary btn-xs">'.$counter.'</span>' : '') . ($item->hasChildren() ? '<span class="fa arrow"></span>' : '') .'</a>' . $sub . '</li>';
                }
            )?>
            <?=$this->menu('dashboard-admin-sidebar', 
                function(string $items, int $level){
                    if($level == 0){
                        return "<ul id=\"side-admin-menu\" class=\"nav nav-admin\">$items</ul>";    
                    }
                    
                    return "<ul class=\"nav nav-second-level\">$items</ul>";    
                }, 

                function($item, $sub) use ($user){
                    if(!$user->isAccess($item->getData('access'))) return;
                    $counter = intval($item->getData('counter'));
                    return '<li><a href="'. $item->getData('url') .'"><i class="fa fa-fw fa-'. $item->getData('fa') .'"></i> '. $item->getTitle() . ($counter > 0 ? ' <span class="counter btn-danger btn-xs">'.$counter.'</span>' : '') . ($item->hasChildren() ? '<span class="fa arrow"></span>' : '') . '</a>' . $sub . '</li>';
                }
            )?>
        <?php endif?>
        <?php $this->hook('navbar.side')?>
    </div>
</div>