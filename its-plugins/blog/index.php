<?php
/**
 * Hooks:
 * @hook post.save (Post $post) // called when post was created
 * @hook template.post.edit	(?Post $post|null, ?SingleUser $author|null); // called at the end of post editing template
 */
namespace tsframe;

use tsframe\module\blog\Post;
use tsframe\module\blog\Blog;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('database');
});

Hook::registerOnce('blog.post.inc', function(Post $post, $params){
	if(isset($params['id'])){
		try {
			$post = Blog::getPostById($params['id']);
			echo $post->getContent(true);
			return true;
		} catch(\Exception $e){

		}
	}
	echo null;
	return false;
});
