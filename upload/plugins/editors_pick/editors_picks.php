<?php

/*
Plugin Name: ClipBucket Editor's Pick Plugin
Description: This plugin is used to display Editor's Pick Player On Home Page and also let you pick videos for editor's pick
Author: Arslan Hassan
Author Website: http://clip-bucket.com/
ClipBucket Version: 2
Version: 1.0
Website: http://clip-bucket.com/
Plugin Type: global
*/


define("CB_EP_BASE",basename(dirname(__FILE__)));

define("CB_EP_BASEDIR",PLUG_DIR.'/'.CB_EP_BASE);
define("CB_EP_BASEURL",PLUG_URL.'/'.CB_EP_BASE);

define("CB_EP_ADMIN_DIR", CB_EP_BASEDIR.'/admin');
define("CB_EP_ADMIN_URL", CB_EP_BASEURL.'/admin');

define("CB_EP_INC_DIR", CB_EP_BASEDIR.'/includes');

assign("ep_admin_dir",CB_EP_ADMIN_DIR);
assign("ep_admin_url",CB_EP_ADMIN_URL);
assign("ep_ajax_url",CB_EP_BASEURL.'/ajax.php');



													   

if(!function_exists('editors_pick'))
{
	
	define("editorspick_install","installed");
	assign("editorspick",EDITORSPICK);
	
	function editors_pick()
	{
		if($_GET['add_editor_pick'])
		{ 
			$vid = mysql_clean($_GET['add_editor_pick']);
			add_to_editor_pick($vid);
		}
		
		if($_GET['remove_editor_pick'])
		{ 
			$vid = mysql_clean($_GET['remove_editor_pick']);
			remove_vid_editors_pick($vid);
		}
	}
	
	/**
	 * Function used to add video to editor's pick;
	 */
	function add_to_editor_pick($vid)
	{
		global $cbvid,$db;
		if($cbvid->video_exists($vid))
		{
			if(!is_video_in_editors_pick($vid))
			{
				$sort = get_highest_sort_number() + 1 ;
				$db->insert(tbl("editors_picks"),array("videoid","sort","date_added"),array($vid,$sort,now()));
				 $db->update(tbl("video"), array("in_editor_pick"), array("yes")," videoid = '".$vid."'");
				e(lang("Video has been added to editor's pick"),"m");
			} else {
				e(lang("Video is already in editor's pick"),"e");
			}
		}else
			e(lang("video_exist_err"));
	}
	
	/**
	 * Remove Video From Editor's Pick
	 */
	function remove_vid_editors_pick($vid)
	{
		global $db;
		if(is_array($vid))
			$vid = $vid['videoid'];
		if(is_video_in_editors_pick($vid))
		{
			$db->delete(tbl('editors_picks'),array('videoid'),array($vid));
			$db->update(tbl("video"), array("in_editor_pick"), array("no")," videoid = '".$vid."'");
			e(lang("Video has been removed from editor's pick"),"m");
		}
	}
	
	
	/**
	 * Function used to check weather video already exisrts in editors pick or not
	 */
	function is_video_in_editors_pick($vid)
	{
		global $db;
		$count = $db->count(tbl("editors_picks"),"videoid"," videoid='$vid'");
		if($count>0)
			return true;
		else
			return false;
	}
	
	/**
	 * Function used to get highest sort number
	 */
	function get_highest_sort_number()
	{
		global $db;
		$result = $db->select(tbl("editors_picks"),"sort",NULL,NULL," sort DESC ");
		return $result[0]['sort'];
	}
	
	/**
	 * Function used to get highest sort number
	 */
	function get_lowest_sort_number()
	{
		global $db;
		$result = $db->select(tbl("editors_picks"),"sort",NULL,NULL," sort ASC ");
		return $result[0]['sort'];
	}
	
	/**
	 * Function used to display video manager link|
	 */
	function video_manager_ep_link($vid)
	{
		if(is_video_in_editors_pick($vid['videoid']))
			return '<li><a role="menuitem" tabindex="-1" href="'.queryString(NULL,
			array('remove_editor_pick','add_editor_pick','mode')).'remove_editor_pick='.$vid['videoid'].'">Remove From Editor\'s Pick</a><li>';
		else
			return '<li><a role="menuitem" tabindex="-1" href="'.queryString(NULL,
			array('remove_editor_pick','add_editor_pick','mode')).'add_editor_pick='.$vid['videoid'].'">Add To Editor\'s Pick</a></li>';
	}
	
	
	
	/**
	 * Function used to get editor picks videos and details
	 */
	function get_ep_videos()
	{
		global $db;
		//$results = $db->select(tbl('editors_picks,video,users'),tbl('editors_picks.*,video.*,users.userid,users.username')," ".tbl('editors_picks').".videoid = ".tbl('video').".videoid AND ".tbl('video.active')." = 'yes' AND ".tbl('video.broadcast')." = 'public' AND ".tbl('video.status')." = 'Successful' AND ".tbl("video.userid")." = ".tbl("users.userid")." ORDER BY ".tbl('editors_picks').".sort ASC");
		//$results = $db->select(tbl('editors_picks,video,users'),tbl('editors_picks.*,video.*,users.userid,users.username')," ".tbl('editors_picks').".videoid = ".tbl('video').".videoid AND ".tbl('video.active')." = 'yes' ORDER BY ".tbl('editors_picks').".sort ASC");
		$results = $db->select(tbl('editors_picks,video,users'),tbl('editors_picks.*,video.*,users.userid,users.username')," ".tbl('editors_picks').".videoid = ".tbl('video').".videoid AND ".tbl('video.active')." = 'yes' AND ".tbl('video.broadcast')." = 'public' AND ".tbl("video.userid")." = ".tbl("users.userid")." ORDER BY ".tbl('editors_picks').".sort ASC");
		//echo $db->db_query;
		//pr($results,true);
		return $results;
	}
	
	/**
	 * Function used to move pic up
	 */
	function move_pick_up($id)
	{
		global $db;
		$result = $db->select(tbl("editors_picks"),"*"," pick_id='$id'");
		if(count($result)>0)
		{
			$result = $result[0];
			$sort = $result['sort'];
			if($sort>get_lowest_sort_number())
			{
				$less_result = $db->select(tbl("editors_picks"),"*"," sort<$sort",1);
				if(count($less_result)>0)
				{
					$less_result = $less_result[0];
					$new_sort = $less_result['sort'];
					
					$db->update(tbl('editors_picks'),array('sort'),$new_sort," pick_id='$id'");
					$db->update(tbl('editors_picks'),array('sort'),$sort," pick_id='".$less_result['pick_id']."'");
				}
			}
		}
	}
	
	
	/**
	 * Function used to move pic up
	 */
	function move_pick_down($id)
	{
		global $db;
		$result = $db->select(tbl("editors_picks"),"*"," pick_id='$id'");
		if(count($result)>0)
		{
			$result = $result[0];
			$sort = $result['sort'];
			if($sort<get_highest_sort_number())
			{
				$less_result = $db->select(tbl("editors_picks"),"*"," sort>$sort",1);
				if(count($less_result)>0)
				{
					$less_result = $less_result[0];
					$new_sort = $less_result['sort'];
					
					$db->update(tbl('editors_picks'),array('sort'),$new_sort," pick_id='$id'");
					$db->update(tbl('editors_picks'),array('sort'),$sort," pick_id='".$less_result['pick_id']."'");
				}
			}
		}
	}
	
	/**
	 * Function used to move editors pick
	 */
	function move_epick($id,$order)
	{
		global $db;
		if(!is_video_in_editors_pick($id))
			e("Video doesnt exist in editor's picks");
		else
		{
			if(!is_numeric($order) || $order <1)
				$order = 1;
			$db->update(tbl("editors_picks"),array("sort"),array($order)," videoid='".$id."'");
		}
	}
	
	/**
	 * Function used to display editors pick
	 */
	function show_editor_pick()
	{
		echo '<div id="editors_pick" style="padding-bottom:10px">
		This content requires JavaScript and Macromedia Flash Player 7 or higher. <a href=http://www.macromedia.com/go/getflash/>Get Flash</a><br/><br/>
		</div>
		<script type="text/javascript">
		var ep = new FlashObject("/plugins/editors_pick/editors_pick_player.swf?xmlfile=/plugins/editors_pick/editors_pick_player.php", "sotester", "340", "243", "9", "#FFFFFF");
        ep.addParam("wmode", "opaque");
        ep.addParam("allowFullScreen", "true");
		ep.write("editors_pick");
		</script>';
	}
	
	

	//the following two written functions are for temporay purpose .. will be chaged later
function admin_area_tab($vid)
	{
		if(is_video_in_editors_pick($vid['videoid']))
			return '<span class="label label-success" >Added to editors pick</span>';
		else
			{}
	}








//Adding Editor's Pick Link
$cbvid->video_manager_link[] = 'video_manager_ep_link';

//Temporay purpose
$cbvid->video_manager_link_new[] = 'admin_area_tab';

//Calling Editor Picks Function
$cbvid->video_manager_funcs[] = 'editors_pick';

//Adding Anchor Function
register_anchor_function(array('show_editor_pick'=>'index_right_top'));
//Registering Delete Action
register_action_remove_video('remove_vid_editors_pick');
//Adding Header.html
$file = PLUG_DIR.'/editors_pick/header.html';
$Cbucket->add_header($file,array('index'));

//Adding Admin Menu
add_admin_menu('Videos','Editor\'s Pick','editor_pick.php',CB_EP_BASE.'/admin');

}
