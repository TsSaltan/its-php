<?php
/**
 * Hooks:
 * @hook post.save (Post $post) // called when post was created
 * @hook template.post.edit	(?Post $post|null, ?SingleUser $author|null); // called at the end of post editing template
 */
namespace tsframe;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('database');
});
