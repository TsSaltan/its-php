<div id="navbar-top-left" class="navbar-header">
    <a class="navbar-brand" href="<?=$siteHome?>">
        <?php if(strpos($siteIcon, '://') !== false):?>
            <img src="<?=$siteIcon?>" class="site-icon" alt="Site icon">
        <?php elseif(strpos($siteIcon, 'fa-') !== false):?>
            <i class="fa <?=$siteIcon?> fa-fw"></i>
        <?php else:?>
            <?=$siteIcon?>
        <?php endif?>
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
    <?php $this->hook('navbar.top')?>
    <?php if($user->isAuthorized()):?>
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
            <i class="fa fa-user fa-fw"></i> <?=((isset($loginEnabled) && $loginEnabled) ? $login : $email)?> <b class="caret"></b>
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
    <?php endif?>
</ul>