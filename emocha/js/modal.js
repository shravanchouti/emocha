$(function(){
	$('#my_modal').on('show.bs.modal', function(e) {
	var tweetId = $(e.relatedTarget).data('tweet-id');
	$(e.currentTarget).find('input[name="tweetId"]').val(tweetId);
	var tweettext = $(e.relatedTarget).data('tweet-text');
	$(e.currentTarget).find('textarea[name="tweettext"]').val(tweettext);

	});
	$('#my_modal_reply').on('show.bs.modal', function(e) {
		var tweetReplyId = $(e.relatedTarget).data('tweetreply-id');
		$(e.currentTarget).find('input[name="tweetReplyId"]').val(tweetReplyId);
	});
	$('#my_modal_profile').on('show.bs.modal', function(e) {
		var tweetReplyId = $(e.relatedTarget).data('tweetreply-id');
		$(e.currentTarget).find('input[name="tweetReplyId"]').val(tweetReplyId);

	});	
	$(".authors_check").click(function(el,ev){		
		if($(".authors_check").is(':checked'))
			$("#authordetails").css("display","inline-block");
		else
			$("#authordetails").css("display","none");
	});	
});
