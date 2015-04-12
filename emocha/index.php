<?php

//Author : Shravan Chouti
//Email: shravanchouti7@gmail.com
//Old Dominion University
//start session
session_start();

//just simple session reset on logout click
if($_GET["reset"]==1)
{
	session_destroy();
	header('Location: ./index.php');
}
ini_set('display_errors', 1);
include_once("config.php");//Configuration file to connect to API
include_once("inc/twitteroauth.php"); //Reference 
include_once('TwitterAPIExchange.php'); //Reference 

// Setting access tokens here
$settings = array(
    'oauth_access_token' => "3156962183-OcgQiVIqPMOKYHJi4ArORz9XuxRzmAbXG2OMGUy",
    'oauth_access_token_secret' => "MGwm9BWk0EqnWFePlNWAL7GAku1NkTbBhol9PsRLTNG70",
    'consumer_key' => "yh2qcFfRRI70H0y9NLCcToBZT",
    'consumer_secret' => "DRKufyEMydcQX4Y8OEtArql8ipdWiMgylQY6OxL1ZJIqGEDDqG"	
);
 if(isset($_SESSION['status']) && $_SESSION['status']=='verified') 
{
	$screenname = $_SESSION['request_vars']['screen_name']; //To dispaly the name of the user on the Nav bar
}
?>

<html>
<head>
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<script type="text/javascript" src="js/jquery-1.10.1.min.js"></script>
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/modal.js"></script>
		<title>emocha</title>
			<!-- Nav Bar-->
				<nav class="navbar navbar-default navbar-static-top">
					<div class="container">
							<div class="navbar-header">
								<a class="navbar-brand" style="font-size:25px"><b>emocha</b></a>
							</div>
								<? if(isset($screenname))
								{?> <!-- displaying the name of the user -->
								<ul class="nav navbar-nav navbar-right">
									<li><a href=""><?= $screenname; ?></a></li>
									<li><a href="index.php?reset=1">Logout</a></li>							
								</ul>		
								<?}?>
					</div>
				</nav>
</head>

<body>
<?php
if(isset($_SESSION['status']) && $_SESSION['status']=='verified') 
{	//Success, redirected back from process.php with varified status.
	//retrive variables
	$screenname 		= $_SESSION['request_vars']['screen_name'];
	$twitterid 			= $_SESSION['request_vars']['user_id'];	
	$oauth_token 		= $_SESSION['request_vars']['oauth_token'];
	$oauth_token_secret = $_SESSION['request_vars']['oauth_token_secret'];
	
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $oauth_token, $oauth_token_secret);
	if(!isset($_SESSION['create_list_count']))
		$_SESSION['create_list_count'] = 1;
	
		//checking for each made for all the possibilities when a connection is made.
		if(isset($_POST['query']) || isset($_POST['saved_query_search']) || isset($_POST['authors']) || isset($_POST['delete_query_id']) || isset($_POST['delete_author_name']) || isset($_POST['save_author']))
		{
				
				if(isset($_POST['query'])){	 //when a post is made to search for a query 
					$q = $_POST['query'];// assign the posted query to a var to pass on to connection	
					$_SESSION['query'] = $_POST['query'];	//setting session query  
				}
				if(isset($_POST['saved_query_search'])){ //when a post is made to save a query
					$q = $_POST['saved_query_search'];			
					$_SESSION['query'] = $_POST['saved_query_search'];//setting session query  
				}
				if($_SESSION['create_list_count'] == 1){ //setting session to 1 for creating lists to each user
					$my_authorsListCreate = $connection->post('lists/create', array('name' => $screenname, 'mode' => 'private'));
					$_SESSION['listId'] = $my_authorsListCreate ->id;
					$_SESSION['listSlug'] = $my_authorsListCreate ->slug;
					$_SESSION['create_list_count'] = 0;  //setting session to other value after creating a list so that it does not create list for each request.
					$q = $_SESSION['query'];
						
				}
				if(isset($_POST['authors'])) {			
					 foreach($_POST['authors'] as $author) { //to add authors to list its returna a two dimensional array 
						foreach($author as $list){				 
								list($Authors,$AuthorUserid) = explode(",",$list);//splitting the array						
									 $listId = $_SESSION['listId'];
									 $listSlug = $_SESSION['listSlug'];
									//Post to create a list for members	
									$my_authorsList = $connection->post('lists/members/create', array('list_id' => $listId, 'slug'=>$listSlug, 'user_id'=>$AuthorUserid, 'screen_name'=>$Authors));
									$q = $_SESSION['query'];
						}         
					}
				}
				if(isset($_POST['delete_query_id'])){ //deleting a saved query
					$query_id = $_POST['delete_query_id'];				
					$my_querydelete = $connection->post('saved_searches/destroy/'.$query_id.'', array()); //Post to remove a saved query
					$q = $_SESSION['query'];	
					}	
				if(isset($_POST['delete_author_id'])){
					$q = $_SESSION['query'];	//after deleting the authors to retrive back the last search query
				}
				if(isset($_POST['save_author']) || isset($_POST['delete_author_name'])){
					$q = $_SESSION['query'];	//getting back last searched authors list after clicking on author name
				}
			
			//Performing search
			$url = 'https://api.twitter.com/1.1/search/tweets.json';
			$getfield = '?q='. urlencode($q) .'&lang=en&result_type=recent';
			$requestMethod = 'GET';
			$twitter = new TwitterAPIExchange($settings);
			$twitter_stream = $twitter->setGetfield($getfield)
									  ->buildOauth($url, $requestMethod)
									  ->performRequest();
						 
			$twitter_data = json_decode($twitter_stream,TRUE); //decode json
			
		}

		if(isset($_POST["tweetId"])){ //Post to retweet
			$id = $_POST["tweetId"];
			$url = 'https://api.twitter.com/1.1/statuses/retweet/'.$id.'.json';
			$postfields = array();		
			$requestMethod = 'POST';
			$twitter = new TwitterAPIExchange($settings);
			$twitter_stream = $twitter->setPostfields($postfields)	
									  ->buildOauthForReTWeet($url, $requestMethod)
								      ->performRequest();

				if($twitter_stream) //check results obtained
				{?>
					<div class="row">
						<div class="col-md-6 col-md-offset-3">
							<div class="alert alert-success alert-dismissible" role="alert">
								<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								Retweet successful!
							</div>
						</div>
					</div>
				<?}
								 
			$saved_query = $_SESSION['query'];		 //Get to search for a query to retrive the last search 
			$url = 'https://api.twitter.com/1.1/search/tweets.json';
			$getfield = '?q='. $saved_query .'&lang=en&result_type=recent';
			$requestMethod = 'GET';
			$twitter = new TwitterAPIExchange($settings);
			$twitter_stream = $twitter->setGetfield($getfield)
									  ->buildOauth($url, $requestMethod)
									  ->performRequest();
								 
			$twitter_data = json_decode($twitter_stream,TRUE);
		}
	
		if(isset($_POST["tweetReplyId"])){ //Post to reply for a tweet
			$test = $_POST["replay_text"];
			//Post text to twitter
			$my_update = $connection->post('statuses/update', array('status' => $_POST["replay_text"]));
			if($my_update){?>
						<div class="row">
							<div class="col-md-6 col-md-offset-3">
								<div class="alert alert-success alert-dismissible" role="alert">
								  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								  Reply successful! Check your notifications.
								</div>
							</div>
						</div>
			<?}
					$saved_query = $_SESSION['query'];			//Get to search for a query to retrive the last search 			
					$url = 'https://api.twitter.com/1.1/search/tweets.json';
					$getfield = '?q='. $saved_query .'&lang=en&result_type=recent';
					$requestMethod = 'GET';
					$twitter = new TwitterAPIExchange($settings);
					$twitter_stream = $twitter->setGetfield($getfield)
								 ->buildOauth($url, $requestMethod)
								 ->performRequest();
								 
					$twitter_data = json_decode($twitter_stream,TRUE);					
		}?>
	<div class="row">
		<div class="col-md-3">		
				<? //post to get authors list
				if(isset($_POST['authors']) || isset($_POST['delete_author_name']) || isset($_POST['saved_query_search']) || isset($_POST['delete_query_id']) || isset($_POST['query']) && isset($_SESSION['query'])){
					?>
					<div class="panel panel-info">
							<div class="panel-heading">
								<center>
								<p>Authors List</p>
								</center>
							</div>
							<div class="panel-body">
								<table>
									<?if(isset($_POST['delete_author_name'])){
									//post to delete authors from saved list
											$author_screenname = $_POST['delete_author_name'];
											$listId = $_SESSION['listId'];
											$listSlug = $_SESSION['listSlug'];
											
											$my_authordelete = $connection->post('lists/members/destroy_all', array('list_id' => $listId, 'slug'=>$listSlug, 'screen_name'=>$author_screenname));
											//get method to list members again after deleting a author from the saved list
											$my_authorsLists = $connection->get('lists/members', array('list_id'=>$listId, 'slug'=>$listSlug));					
									}
									else{
											$listId = $_SESSION['listId'];
											$listSlug = $_SESSION['listSlug'];
									
											//get method to list members after adding a member to list
											$my_authorsLists = $connection->get('lists/members', array('list_id'=>$listId, 'slug'=>$listSlug));							
									} //Displaying each author 
									foreach($my_authorsLists->users as $my_authorsList){?>					
										<tr>
											<td>
												<!-- Printing authors list-->
												<form method="post" action="">
													<!-- Button dispaly author name and add more functionality-->
													<button class="btn btn-default" type="submit" style="margin-top:-20px" name="save_author" value="<? echo $my_authorsList->screen_name;?>" disabled><? echo $my_authorsList->screen_name;?></button>
													<!-- Follow user iframe used from twitter website-->			
															<a class="twitter-follow-button" href="https://twitter.com/<? echo $my_authorsList->screen_name;?>" data-lang="en" data-show-screen-name="false" data-show-count="false" data-size="large">		
															Follow
															</a>
															<script>window.twttr=(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],t=window.twttr||{};if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);t._e=[];t.ready=function(f){t._e.push(f);};return t;}(document,"script","twitter-wjs"));</script>		
															
													<form method="post">
														<!-- Form to delete author-->										
														<button  type="submit" name="delete_author_name" style="margin-top:-20px" class="btn btn-default" value="<? echo $my_authorsList->screen_name;?>"><span class="glyphicon glyphicon-remove"></span></button>
													</form>
												</form>
											</td>
										</tr>
									<? }?>
								</table>
							</div>
					</div>
					<?}?>
		</div>
		<div class="col-md-7">
			
			<div class="col-lg-8">
				<!-- Form to post the search query-->
				<form method="post" action="">
					<div class="input-group input-group-lg">		
					  <input type="text" class="form-control" name="query" placeholder="Search for tweets...">
					  <span class="input-group-btn">
						<button class="btn btn-primary" type="submit" name="search">Search</button>
					  </span>	 
					</div>
				</form>
			</div>
		
				<?//Checking all possible posts to get back the last searched results
				if((isset($_POST['search']) ? $_POST['query'] : null) || isset($_POST["tweetId"]) || isset($_POST['tweetReplyId']) || isset($_POST['saved_query_search']) || isset($_POST['authors']) || isset($_POST['delete_query_id']) || isset($_POST['delete_author_name']))
					{?>
						<div class="panel panel-info">
							<div class="panel-heading">
								<center>
								<h3>Results for "<b><?= $_SESSION['query'];?></b>"</h3>
								</center>
							</div>
							<div class="panel-body">
								<table class="table table-stripped">
								<!-- Displaying the search results-->
									<?for($i=0;$i<count($twitter_data["statuses"]);$i++){?>
									<tr>
										<td width ="5%">
											<!-- Form to get author details with checkbox-->
											<form method="post" action="">	
											<input type="checkbox"  class="authors_check" name="authors[][]" value="<?= $twitter_data["statuses"][$i]["user"]["screen_name"];?>,<? echo $data_id = $twitter_data["statuses"][$i]["user"]["id"] ?>">	
										</td>
									
										<td width="10%">
											<!-- Getting profile picture -->										
											<img src="<?= $twitter_data["statuses"][$i]["user"]["profile_image_url_https"]; ?>" class="thumbnail">
										</td>
										<td width="80%">
											<!--Displaying username and text -->
											<b><a href="" data-target="#my_modal_profile" data-toggle="modal" data-tweetreply-id="<? echo $data_id = $twitter_data["statuses"][$i]["id"] ?>">
											<?= $twitter_data["statuses"][$i]["user"]["name"]; ?></a></b><?= $twitter_data["statuses"][$i]["user"]["screen_name"]; ?>
												<!-- Displaying time of tweet-->
												<div class="pull-right"><?$time = $twitter_data["statuses"][$i]["user"]["created_at"];echo date ("F j Y, g:i a",strtotime($time));?>
												</div>
												<br>
														<?php
																//regular Expression filter
																$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
																
																$text = $twitter_data["statuses"][$i]["text"];
																// checking if there is a url in the text
																if(preg_match($reg_exUrl, $text, $url)) {
																	   // make the urls hyper links
																	   echo preg_replace($reg_exUrl, '<a href="'.$url[0].'" rel="nofollow">'.$url[0].'</a>', $text);
																} else {
																// if no urls in the text just return the text
																echo $text;
																}								
														?>
												<br>
											<!--Reply to a single tweet -->
											<a href="#my_modal_reply" data-toggle="modal" data-tweetreply-id="<? echo $data_id = $twitter_data["statuses"][$i]["id"] ?>" style="margin-right:50px">
												<input type="hidden" data-tweet-username="<?= $twitter_data["statuses"][$i]["user"]["screen_name"]; ?>"><img src="images/reply.png" width="25" height="25">
											</a>
											<!-- Retweet a single tweet -->
											<a href="#my_modal" data-toggle="modal" data-tweet-id="<? echo $data_id = $twitter_data["statuses"][$i]["id"] ?>" data-tweet-text="<?= $twitter_data["statuses"][$i]["text"];?>">
												<input type="hidden" data-tweet-username="<?= $twitter_data["statuses"][$i]["user"]["screen_name"]; ?>"><img src="images/retweet.png" width="25" height="25">
											</a>
											<!-- Follow user iframe used from twitter website-->
											<div class="pull-right">
												<a class="twitter-follow-button" href="https://twitter.com/<?= $twitter_data["statuses"][$i]["user"]["screen_name"]; ?>" data-show-count="true" data-lang="en" style="margin-left:50px" data-size="large">
												<?= $twitter_data["statuses"][$i]["user"]["name"]; ?>
												</a>
												<script>window.twttr=(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],t=window.twttr||{};if(d.getElementById(id))return t;js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);t._e=[];t.ready=function(f){t._e.push(f);};return t;}(document,"script","twitter-wjs"));</script>
											</div>
										</td>
									</tr>
									<?}
									
									?>
								</table>
							</div>
						</div>						<!-- Button to get authors details and adding them to list -->
												<button class="btn btn-primary" id="authordetails" style="display:none">Get Author Details</button>
											</form>
					<?}?>
		</div>
		<? if(isset($_SESSION['query']))
		{
			$q = $_SESSION['query'];	
			$my_create = $connection->post('saved_searches/create', array('query' => $q));
			//post to create list to save list
			$my_lists = $connection->get('saved_searches/list', array()); //get method to save a query to list?>
		<div class="col-md-2">			
			<div class="panel panel-info">
				<div class="panel-heading">
					<center>
						<p>Recent Search</p>
					</center>
				</div>
				<div class="panel-body">
					<table><!-- Listing the saved queries-->
							<? foreach($my_lists as $my_list)
							{?>
								<tr>
									<td>
									<form method="post" action="">
										<button class="btn btn-default" type="submit" name="saved_query_search" value="<? echo $my_list->query; ?>"><? echo $my_list->query; ?></button>
											<form method="post">				
												<button  type="submit" name="delete_query_id"class="btn btn-default" value="<? echo $my_list->id; ?>"><span class="glyphicon glyphicon-remove"></span>
												</button>
											</form>
									</form>
									</td>
								</tr>
							<?}?>
					</table>
				</div>
			</div>
		</div>
		<?}?>
	</div>
	<!-- Modal to retweet-->
	<div class="modal" id="my_modal">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				  <h4 class="modal-title">Retweet this to your followers</h4>
			  </div>
			  <div class="modal-body">		  
				<input type="hidden" name="tweetId"/>			
				<textarea class="form-control" name="tweettext" rows="5" Value=""></textarea>	
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary">Retweet</button>
			  </div>
			</form>
		</div>
	  </div>
	</div>
	<!-- Modal to reply-->
	<div class="modal" id="my_modal_reply">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				 <h4 class="modal-title">Reply</h4>
			  </div>
			  <div class="modal-body">
				<input type="hidden" name="tweetReplyId"/>
				<textarea class="form-control" name="replay_text" rows="5" placeholder="Reply..."></textarea>	
			  </div>
			  <div class="modal-footer">
			   <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary">Reply</button>
			  </div>
			</form>
		</div>
	  </div>
	</div>

<?php			
}
else
{?>
	<!--Home page before login -->	
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<center>
				<a href="process.php"><img src="images/twitter-logo.png"/></a>
			</center>
		</div>
	</div>
<?
}
?>
</body>
</html>
