$(function() {
    $('.sidebar ul.nav').metisMenu();
});

//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function() {
    $(window).bind("load resize", function() {
        topOffset = 50;
        width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }
    });

    var url = window.location;

    var element = $('ul.nav a').filter(function() {
        return this.href == url || url.href.indexOf(this.href) == 0;
    }).addClass('active').parent().parent().addClass('in').parent();
    if (element.is('li')) {
        element.addClass('active');
    }

    if(window.location.hash){
        let hash = window.location.hash;

        // Раскрываем свёрнутые панели
        if($(hash+".collapse").length) {
            $(hash+".collapse").removeClass('collapse');
        } 

        // Раскрываем табы
        else if($('a[href="'+hash+'"]')){
            $('a[href="'+hash+'"]').click();
        }
    }

    // Сохраняем в url нажатые ссылки на табы, панели и т.д.
    $('a[href^="#"]').click(function(e){
        setHash(e.target.hash);
    });
});

function setHash(hash){
    if(hash.charAt(0) != '#') hash = '#' + hash;

    if(history.pushState) {
        history.pushState(null, null, hash);
    }
    else {
        location.hash = hash;
    }
}
