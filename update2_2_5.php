<?php
require_once('includes/upb.initialize.php');
$from_version = UPB_VERSION;
$to_version = "2.2.5";

require_once('includes/class/posts.class.php');

$posts_tdb = new posts(DB_DIR."/", "posts.tdb");
$where = "Updating $from_version to $to_version";
?>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<title>UPB v2.2.5 Updater</title>
<link rel='stylesheet' type='text/css'
	href='./skins/default/css/style.css' />
</head>
<body>
<div id='upb_container'>
<div class='main_cat_wrapper2'>
<table class='main_table_2'>
	<tr>
		<td id='logo'><img src='./skins/default/images/logo.png' alt=''
			title='' /></td>
	</tr>
</table>
</div>
<br />
<br />
<div class='main_cat_wrapper'>
<div class='cat_area_1'>myUPB v2.2.5 Updater</div>
<table class='main_table'>
	<tr>
		<th style='text-align: center;'>&nbsp;</th>
	</tr>
	<tr>
		<td class='area_welcome'>
		<div class='welcome_text'>If you had any problems, please seek support
		at <a href='http://forum.myupb.com/'>myupb.com's support forums!</a></div>
		</td>
	</tr>
	<tr>
		<td class='footer_3'><img src='./skins/default/images/spacer.gif'
			alt='' title='' /></td>
	</tr>
	<tr>
		<td class='area_2'
			style='text-align: center; font-weight: bold; padding: 12px; line-height: 20px;'>
		<p><?php echo $where; ?>
		
		
		<p>Fixing post counts .... <?php

		$userlist = $tdb->query('users',"id>'0'",1,-1,array('user_name','id','posts'));

		$forumlist = $tdb->query('forums',"id>'0'",1,-1,array('id','forum','topics','posts'));

		$postcount = array();

		foreach ($forumlist as $forum)
		{

			$posts_tdb->setFp("topics", $forum['id']."_topics");
			$posts_tdb->setFp("posts", $forum["id"]);
			$posts = $posts_tdb->query('posts',"id>'0'",1,-1,array('user_name','user_id','id'));
			$topics = $posts_tdb->query('topics',"id>'0'");

			$tdb->edit('forums',$forum['id'],array('topics'=>count($topics),'posts'=>count($posts)));

			if ($posts !== false)
			{
				foreach ($posts as $post)
				{
					$postcount[$post['user_id']] = $postcount[$post['user_id']] + 1;
				}
			}
		}

		foreach ($postcount as $key => $value)
		{
			$uquery = $tdb->basicQuery('users','id',$key);
			if ($uquery !== false)
			$tdb->edit('users',$key,array('posts'=>$value));
		}
		echo "done";
		?>
		
		
		<p><input type='button' onclick="location.href='complete_update.php'"
			value='Click here to proceed to next step'>
		
		</td>
	</tr>
	<tr>
		<td class='footer_3'><img src='./skins/default/images/spacer.gif'
			alt='' title='' /></td>
	</tr>
</table>
<div class='footer'><img src='./skins/default/images/spacer.gif' alt=''
	title='' /></div>
</div>
<br />
<div class='copy'>Powered by myUPB&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a
	href='http://www.myupb.com/'>PHP Outburst</a> &nbsp;&nbsp;&copy;2002 -
		<?php echo date("Y",time()); ?></div>
</div>
</body>
</html>
