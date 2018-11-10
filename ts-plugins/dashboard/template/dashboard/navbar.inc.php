    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" id="navbar-top" role="navigation">
        <div id="navbar-top-left" class="navbar-header">
            <a class="navbar-brand" href="/"><i class="fa fa-home fa-fw"></i> На главную</a>
        </div>

        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>

        <!-- Top Navigation: Right Menu -->
        <ul id="navbar-top-right" class="nav navbar-right navbar-top-links">
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    <i class="fa fa-user fa-fw"></i> <?=$this->login?> <b class="caret"></b>
                </a>
                <?=$this->menu('dashboard-top', 
                    function(string $items){
                        return "<ul class=\"dropdown-menu dropdown-user\">$items</ul>";
                    }, 

                    function($item){
                        return '<li><a href="'. $item->getData('url') .'"><i class="fa fa-fw fa-'. $item->getData('fa') .'"></i> '. $item->getTitle() .'</a></li>';
                    }
                )?>
            </li>
        </ul>

        <!-- Sidebar -->
        <div id="sidebar" class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <?=$this->menu('dashboard-sidebar', 
                    function(string $items, int $level){
                        if($level == 0){
                            return "<ul class=\"nav\" id=\"side-menu\">$items</ul>";    
                        }
                        
                        return "<ul class=\"nav nav-second-level\">$items</ul>";    
                    }, 

                    function($item, $sub){
                        if(!tsframe\module\user\UserAccess::checkCurrentUser($item->getData('access'))) return;
                        return '<li><a href="'. $item->getData('url') .'"><i class="fa fa-fw fa-'. $item->getData('fa') .'"></i> '. $item->getTitle() . ($item->hasChildren() ? '<span class="fa arrow"></span>' : '') . '</a>' . $sub . '</li>';
                    }
                )?>

                <?=$this->menu('dashboard-admin-sidebar', 
                    function(string $items, int $level){
                        if($level == 0){
                            return "<ul id=\"side-admin-menu\" class=\"nav nav-admin\">$items</ul>";    
                        }
                        
                        return "<ul class=\"nav nav-second-level\">$items</ul>";    
                    }, 

                    function($item, $sub){
                        if(!tsframe\module\user\UserAccess::checkCurrentUser($item->getData('access'))) return;
                        return '<li><a href="'. $item->getData('url') .'"><i class="fa fa-fw fa-'. $item->getData('fa') .'"></i> '. $item->getTitle() . ($item->hasChildren() ? '<span class="fa arrow"></span>' : '') . '</a>' . $sub . '</li>';
                    }
                )?>
            </div>
        </div>
    </nav>