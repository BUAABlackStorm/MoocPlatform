/*
 *	www.templatemo.com
 *******************************************************/

/* HTML document is loaded. DOM is ready. 
-----------------------------------------*/
$(document).ready(function(){

	/* Mobile menu */
	$('.mobile-menu-icon').click(function(){
		$('.templatemo-left-nav').slideToggle();				
	});

	/* Close the widget when clicked on close button */
	$('.templatemo-content-widget .fa-times').click(function(){
		$(this).parent().slideUp(function(){
			$(this).hide();
		});
	});
	$('#download').click(function(){
		var p=$("#files input").is(':checked');
		if(p==false) alert('请至少选择一个下载项目！');
		});
	$('#delete').click(function(){
		var p=$("#deletes input").is(':checked');
		if(p==false) alert('请至少选择一个删除项目！');
		});
	});

