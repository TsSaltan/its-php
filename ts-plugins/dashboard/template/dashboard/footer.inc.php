</div>

<?php $this->hook("footer")?>
<script type="text/javascript">
	function toggleSidebar(){
		$('#sidebar').toggleClass('collapse-width'); 
		$('#page-wrapper').toggleClass('full-width');
		try {
			sessionStorage.setItem('collapsedSidebar', $('#page-wrapper').hasClass('full-width'));
		} catch (e){
		}
	}

	$(function(){
		try {
			let collapsed = sessionStorage.getItem('collapsedSidebar');
			if(collapsed !== false && collapsed !== 'false'){
				toggleSidebar();
			}
		} catch (e){

		}
	});
</script>
</body>
</html>