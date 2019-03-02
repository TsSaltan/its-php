<div id="navbar-top-left" class="navbar-header">
    <a class="navbar-brand" href="<?=$siteHome?>">
        <?if(strpos($siteIcon, '://') !== false):?>
            <img src="<?=$siteIcon?>" class="site-icon" alt="Site icon">
        <?elseif(strpos($siteIcon, 'fa-') !== false):?>
            <i class="fa <?=$siteIcon?> fa-fw"></i>
        <?else:?>
            <?=$siteIcon?>
        <?endif?>
        <?=$siteName?>
    </a>
</div>

<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
    <span class="sr-only">Toggle navigation</span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
</button>

<!-- Top Navigation: Right Menu -->
<ul id="navbar-top-right" class="nav navbar-right navbar-top-links">
    <?$this->hook('navbar.top')?>
    <?if($user->isAuthorized()):?>
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
            <i class="fa fa-user fa-fw"></i> <?=($loginUsed ? $login : $email)?> <b class="caret"></b>
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
    <?endif?>
</ul>