<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 *
 */

define('IN_MYBB', 1);
require_once './global.php';

//Make TimeZone List

$timezones = array(
	"BST",
	"EST",
	"GMT",
);

add_breadcrumb('Event Management System', "spartian_cp_plugin.php");

$timeZoneDropDown = "";

foreach($timezones as $timezone) {
	$timeZoneDropDown .= "<option value=\"{$timezone}\">{$timezone}</option>";
}

$post_report = "";

if($mybb->input['create_event']) {
	
	add_breadcrumb('Create Events', "spartian_cp_plugin.php?action=add_event");
	
	$cache = array();
	$cache["host"] = $mybb->user['uid'];
	$cache["time"] = $mybb->input['time'];
	$cache["date"] = $mybb->input['date'];
	$cache["title"] = $mybb->input['title'];
	$cache["timezone"] = $mybb->input['timeZone'];
	$cache["location"] = $mybb->input['location'];
	$cache["special_instructions"] = $mybb->input['special_instructions'];
	
	$queryArray = array("date" => $mybb->input['date'], "cache" => json_encode($cache));
	
	if($db->insert_query("zcombat_events", $queryArray))
	{
		$post_report = "Event Successfully created!";
	}
	else
	{
		$post_report = "Your attempt to make an event was a big fail.";
	}
	
}
$eventTable = '';

if(empty($mybb->input['action'])) {
	
	
	
	//Revier TYable
			//if Registering
			if (isset($mybb->input['register_for_event']))
			{
				$eventArray = array('userId' => $mybb->user['uid'], 'eventID' => $mybb->input['register_for_event']);
				if($db->insert_query('zcombat_participants', $eventArray))
				{
					//Do Nothing
				}
				else
				{
					//Do Nothing
				}
			}
	//Build query
	//$query = $db->simple_select('zcombat_events', 'cache', '`status` = 1', 'ORDER BY `date` ASC');
	$query = $db->write_query("SELECT * FROM `mybb_zcombat_events` WHERE `status` = 1 ORDER BY `date` ASC");

	while($cacheJSON = $db->fetch_array($query)) {
		$cache = json_decode($cacheJSON['cache'], true);
		
		$u = get_user($cache['host']);
		
		$sqlCount = $db->write_query("SELECT * FROM `mybb_zcombat_participants` WHERE `eventId` = {$cacheJSON['id']}");
		$count = $sqlCount->num_rows;
		
		$sqlIfParticipant = $db->write_query("SELECT * FROM `mybb_zcombat_participants` WHERE `userId` = {$mybb->user['uid']} AND `eventId` = {$cacheJSON['id']}");
			$rowcount = $sqlIfParticipant->num_rows;
			if($rowcount >= 1)
			{
				$signUp = 'Registered!';
			}
			else
			{
				$signUp = "<a href=\"spartian_cp_plugin.php?register_for_event={$cacheJSON['id']}\">Sign Up!</a>";
			}
		$classTD = 'trow1';
		
		if($mybb->user['uid'] == $cache['host'])
		{
			$classTD = 'tcat';
		}
		
		$eventTable .= "
			<tr>
				<td class=\"{$classTD}\" align=\"center\" valign=\"top\">{$cache['date']} {$cache['time']} {$cache['timezone']}<br />{$u['username']}</td>
				<td class=\"{$classTD}\" align=\"center\" valign=\"top\">{$cache['title']} <br /> {$cache['special_instructions']}</td>
				<td class=\"{$classTD}\" align=\"center\" valign=\"top\">{$cache['location']}</td>
				<td class=\"{$classTD}\" align=\"center\" valign=\"top\">{$signUp} (<a href='spartian_cp_plugin.php?action=participats&eventID={$cacheJSON['id']}'>{$count}</a>)</td>
			</tr>
			";
	}
	
add_breadcrumb('Upcoming Events', "spartian_cp_plugin.php");
$form .= "
			<table border=\"0\" cellspacing=\"0\" width=\"100%\" cellpadding=\"5\" class=\"tborder\">	
				<thead><tr>
					<td class=\"thead\" colspan=\"7\">
						<div><strong><img src=\"skills/overall.gif\">Upcoming Events</a></strong></div>
					</td>
				</thead></tr>
					
					<tr>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Date/Host</td>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Title/Description</td>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Location</td>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Register</td>
					</tr>
					
					{$eventTable}
					
				</tbody>
			</table>";
}
//End Index

//Start Past Events
if(isset($mybb->input['action']) && $mybb->input['action'] == 'past_events') {
		//if Registering
	
		add_breadcrumb('Past Participated Events', "spartian_cp_plugin.php?action=past_events");
	//Load Sql
	$query = $db->write_query("SELECT * FROM `mybb_zcombat_participants` WHERE `userId` = {$mybb->user['uid']}");

	while($fetchParticipant = $db->fetch_array($query))
	{
		
		if (isset($mybb->input['unregister_for_event']) && $mybb->input['unregister_for_event'] == $fetchParticipant['id'] && $fetchParticipant['userId'] == $mybb->user['uid'])
		{
			$eventArray = "`id` = '{$fetchParticipant['id']}'";
				if($db->delete_query('zcombat_participants', $eventArray))
				{
					//
				}
				else
				{
					//
				}
		}
		else
		{
			
			//Load Each Event
			$queryEvent = $db->write_query("SELECT * FROM `mybb_zcombat_events` WHERE `id` = {$fetchParticipant['eventID']} AND `status` = 0 LIMIT 1");
			if($fetchEvent = $db->fetch_array($queryEvent))
			{
				$sqlCount = $db->write_query("SELECT * FROM `mybb_zcombat_participants` WHERE `eventId` = {$fetchParticipant['eventID']}");
				$count = $sqlCount->num_rows;
				$cache = json_decode($fetchEvent['cache'], true);
				$eventTable .= 			"<tr>
					<td class=\"trow1\" align=\"center\" valign=\"top\">{$cache['date']} {$cache['time']} <br />{$cache['timezone']}</td>
					<td class=\"trow1\" align=\"center\" valign=\"top\">{$cache['title']} <br /> {$cache['special_instructions']}</td>
					<td class=\"trow1\" align=\"center\" valign=\"top\">{$cache['location']}</td>
					<td class=\"trow1\" align=\"center\" valign=\"top\">Closed ({$count})</td>
				</tr>";
			}
		}
	}
	
$form .= "
			<table border=\"0\" cellspacing=\"0\" width=\"100%\" cellpadding=\"5\" class=\"tborder\">	
				<thead><tr>
					<td class=\"thead\" colspan=\"7\">
						<div><strong><img src=\"skills/overall.gif\">Past Participated Events</a></strong></div>
					</td>
				</thead></tr>
					
					<tr>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Date</td>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Title</td>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Location</td>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Register</td>
					</tr>
					
					{$eventTable}
					
				</tbody>
			</table>";

}

//End Past Events

if(isset($mybb->input['action']) && $mybb->input['action'] == "add_event") {
	add_breadcrumb('Add Events', "spartian_cp_plugin.php?action=add_event");
$form .= "{$post_report}<form action=\"spartian_cp_plugin.php?action=add_event\" method=\"POST\">
			<table border=\"0\" cellspacing=\"0\" width=\"100%\" cellpadding=\"5\" class=\"tborder\">
				<thead><tr>
					<td class=\"thead\" colspan=\"7\">
						<div><strong><img src=\"skills/overall.gif\">Add Event</a></strong></div>
					</td>
				</thead></tr>
				
				<tbody>
						<tr><td class=\"tcat\" align=\"left\" valign=\"top\">Date</td></tr>
						<tr><td class=\"trow1\" align=\"left\" valign=\"top\"><input type=\"date\" name=\"date\" min=\"2015-01-02\"></td></tr>
						
						<tr><td class=\"tcat\" align=\"left\" valign=\"top\">Time</td></tr>
						<tr><td class=\"trow1\" align=\"left\" valign=\"top\"><input type=\"time\" name=\"time\"></td></tr>
						
						<tr><td class=\"tcat\" align=\"left\" valign=\"top\">Time Zone</td></tr>
						<tr><td class=\"trow1\" align=\"left\" valign=\"top\"><select name=\"timeZone\">{$timeZoneDropDown}</select></td></tr>
						
						<tr><td class=\"tcat\" align=\"left\" valign=\"top\">Title</td></tr>
						<tr><td class=\"trow1\" align=\"left\" valign=\"top\"><input type=\"text\" name=\"title\" /></td></tr>
						
						<tr><td class=\"tcat\" align=\"left\" valign=\"top\">Location</td></tr>
						<tr><td class=\"trow1\" align=\"left\" valign=\"top\"><input type=\"text\" name=\"location\" /></td></tr>
						
						<tr><td class=\"tcat\" align=\"left\" valign=\"top\">Special Instructions</td></tr>
						<tr><td class=\"trow1\" align=\"left\" valign=\"top\"><input type=\"text\" name=\"special_instructions\" /></td></tr>
						
						<tr><td class=\"tcat\" align=\"left\" valign=\"top\" colspan=\"2\">Action</td></tr>
						<tr><td class=\"trow1\" align=\"left\" valign=\"top\" colspan=\"2\"><input type=\"submit\" name=\"create_event\" value=\"Create Event\"></td></tr>

				</tbody>
			</table></form>";
}


//Show Participating
if(isset($mybb->input['action']) && $mybb->input['action'] == 'participating')
{
	
		//if Registering
	
		add_breadcrumb('Add Events', "spartian_cp_plugin.php?action=participating");
	//Load Sql
	$query = $db->write_query("SELECT * FROM `mybb_zcombat_participants` WHERE `userId` = {$mybb->user['uid']}");

	while($fetchParticipant = $db->fetch_array($query))
	{
		
		if (isset($mybb->input['unregister_for_event']) && $mybb->input['unregister_for_event'] == $fetchParticipant['id'] && $fetchParticipant['userId'] == $mybb->user['uid'])
		{
			$eventArray = "`id` = '{$fetchParticipant['id']}'";
				if($db->delete_query('zcombat_participants', $eventArray))
				{
					//
				}
				else
				{
					//
				}
		}
		else
		{
			
			//Load Each Event
			$queryEvent = $db->write_query("SELECT * FROM `mybb_zcombat_events` WHERE `id` = {$fetchParticipant['eventID']} AND `status` = 1 LIMIT 1");
			if($fetchEvent = $db->fetch_array($queryEvent))
			{
				$sqlCount = $db->write_query("SELECT * FROM `mybb_zcombat_participants` WHERE `eventId` = {$fetchParticipant['eventID']}");
				$count = $sqlCount->num_rows;
				$cache = json_decode($fetchEvent['cache'], true);
				$eventTable .= 			"<tr>
					<td class=\"trow1\" align=\"center\" valign=\"top\">{$cache['date']} {$cache['time']} <br />{$cache['timezone']}</td>
					<td class=\"trow1\" align=\"center\" valign=\"top\">{$cache['title']} <br /> {$cache['special_instructions']}</td>
					<td class=\"trow1\" align=\"center\" valign=\"top\">{$cache['location']}</td>
					<td class=\"trow1\" align=\"center\" valign=\"top\"><a href=\"spartian_cp_plugin.php?action=participating&unregister_for_event={$fetchParticipant['id']}\">Unregister</a> (<a href='spartian_cp_plugin.php?action=participats&eventID={$fetchParticipant['eventID']}'>{$count})</a></td>
				</tr>";
			}
		}
	}
	
$form .= "
			<table border=\"0\" cellspacing=\"0\" width=\"100%\" cellpadding=\"5\" class=\"tborder\">	
				<thead><tr>
					<td class=\"thead\" colspan=\"7\">
						<div><strong><img src=\"skills/overall.gif\">Participating</a></strong></div>
					</td>
				</thead></tr>
					
					<tr>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Date</td>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Title</td>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Location</td>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Register</td>
					</tr>
					
					{$eventTable}
					
				</tbody>
			</table>";
}
//End Show Participating

//Show Whos Going
if(isset($mybb->input['action']) && $mybb->input['action'] == 'participats')
{
	
		//if Registering
	
		add_breadcrumb('Add Events', "spartian_cp_plugin.php?action=participats");
	//Load Sql
	$query = $db->write_query("SELECT * FROM `mybb_zcombat_participants` WHERE `eventID` = {$mybb->input['eventID']}");

	while($fetchParticipant = $db->fetch_array($query))
	{
		
		if (isset($mybb->input['unregister_for_event']) && $mybb->input['unregister_for_event'] == $fetchParticipant['id'] && $fetchParticipant['userId'] == $mybb->user['uid'])
		{
			$eventArray = "`id` = '{$fetchParticipant['id']}'";
				if($db->delete_query('zcombat_participants', $eventArray))
				{
					//
				}
				else
				{
					//
				}
		}
		else
		{
			$p_username = get_user($fetchParticipant['userId']);
			
				$eventTable .= 			"<tr>
					<td class=\"trow1\" align=\"center\" valign=\"top\">{$p_username['username']}</td>
					<td class=\"trow1\" align=\"center\" valign=\"top\">[Unregister]</td>
				</tr>";
		}
	}
	
$form .= "
			<table border=\"0\" cellspacing=\"0\" width=\"100%\" cellpadding=\"5\" class=\"tborder\">	
				<thead><tr>
					<td class=\"thead\" colspan=\"7\">
						<div><strong><img src=\"skills/overall.gif\">Participating</a></strong></div>
					</td>
				</thead></tr>
					
					<tr>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Username</td>
						<td class=\"tcat\" align=\"center\" valign=\"top\">Register</td>
					</tr>
					
					{$eventTable}
					
				</tbody>
			</table>";
}
//End Show Whos Going

$mods = array(1,2023,304);

$modMenu = "";

foreach($mods as $mod)
{

	if($mybb->user['uid'] == $mod)
	{
		$modMenu = "						<tr>
								<td class=\"thead\"><strong>Mod CP</strong></td>
							</tr>
							<tr>
								<td class=\"trow1 smalltext\"><a class=\"usercp_nav_item usercp_nav_home\" href=\"spartian_cp_plugin.php?action=add_event\">Add Events</a></td>
							</tr>
							<tr>
								<td class=\"trow1 smalltext\"><a class=\"usercp_nav_item usercp_nav_home\" href=\"spartian_cp_plugin.php?action=add_member_to_event\">Add Member To Event</a></td>
							</tr>
							<tr>
								<td class=\"trow1 smalltext\"><a class=\"usercp_nav_item usercp_nav_home\" href=\"spartian_cp_plugin.php?action=edit_events\">Edit Events</a></td>
							</tr>
							<tr>
								<td class=\"trow1 smalltext\"><a class=\"usercp_nav_item usercp_nav_home\" href=\"spartian_cp_plugin.php?action=delete_event\">Delete Events</a></td>
							</tr>
							<tr>
								<td class=\"trow1 smalltext\"><a class=\"usercp_nav_item usercp_nav_home\" href=\"spartian_cp_plugin.php?action=look_up\">Look Up User</a></td>
							</tr>
							<tr>
								<td class=\"trow1 smalltext\"><a class=\"usercp_nav_item usercp_nav_home\" href=\"spartian_cp_plugin.php?action=approve_close\">Approve & Close Event</a></td>
							</tr>";
							
		if(isset($mybb->input['action']) && $mybb->input['action'] == 'approve_close')
		{
			//Check If Event Was Submitted.
			
			if(isset($mybb->input['eventID']))
			{
				$userIDList = $mybb->input['userID'];
				$query = $db->write_query("SELECT * FROM `mybb_zcombat_events` WHERE `status` = 1 AND id = {$mybb->input['eventID']} LIMIT 1");
				
				if($results = $db->fetch_array($query))
				{
					$query = $db->write_query("SELECT * FROM `mybb_zcombat_participants` WHERE `eventID` = {$mybb->input['eventID']}");
					while($fetchParticipant = $db->fetch_array($query)) {
						if(!in_array($fetchParticipant['userId'], $userIDList))
						{
							$eventID = "`id` = '{$fetchParticipant['id']}'";
							$db->delete_query('zcombat_participants', $eventID);
						}
						else
						{
							$db->write_query("UPDATE mybb_users SET `newpoints` = newpoints+20 WHERE `uid` = {$fetchParticipant['userId']}");
						}
					}
					//Close Event
					$db->update_query('zcombat_events', array('status' => 0), "`id` = {$mybb->input['eventID']}");
				}
			}
			
			$tableEvents = '';
			
			//Load Active Events
			$query = $db->write_query("SELECT * FROM `mybb_zcombat_events` WHERE `status` = 1 ORDER BY `date` ASC");

			while($cacheJSON = $db->fetch_array($query)) {
				$cache = json_decode($cacheJSON['cache'], true);
				//Create Tables
				$tableEvents .= "<tr><td class=\"tcat\" align=\"left\" valign=\"top\" colspan=\"4\">
				<form action=\"spartian_cp_plugin.php?action=approve_close\" method=\"POST\">
				{$cache['title']}</td></tr>";
				
				//Query Ea User For Events.
					$query1 = $db->write_query("SELECT * FROM `mybb_zcombat_participants` WHERE `eventID` = {$cacheJSON['id']}  ORDER BY `userId` ASC");
					$int = 0;
						
					while($cacheJSONp = $db->fetch_array($query1)) {
						$u = get_user($cacheJSONp['userId']);
						//Create Tables
							$int++;
							if($int == 1)
							{
								$tableEvents .= "<tr>";
							}
							
							$tableEvents .= "<td class=\"trow1\" align=\"center\" valign=\"top\"><input type=\"checkbox\" name=\"userID[]\" value=\"{$cacheJSONp['userId']}\">{$u['username']}</td>";
							
							if($int == 4)
							{
								$tableEvents .= "</tr>";
								$int = 0;
							}
					}
					$tableEvents .= "<tr><td class=\"trow1\" align=\"left\" valign=\"top\"><input type=\"hidden\" name=\"eventID\" value=\"{$cacheJSON['id']}\"><input type=\"submit\" name=\"add_member\" value=\"Approve Event & Close\"></form></td></tr>";
			}
		$form .= "
			
			<table border=\"0\" cellspacing=\"0\" width=\"100%\" cellpadding=\"5\" class=\"tborder\">
				<thead><tr>
					<td class=\"thead\" colspan=\"4\">
						<div><strong><img src=\"skills/overall.gif\">Approve Event</a></strong></div>
					</td>
				</thead></tr>
				
				<tbody>
						{$tableEvents}
				</tbody>
			</table>";
		}
							
							
		if(isset($mybb->input['action']) && $mybb->input['action'] == 'add_member_to_event')
		{
			//add_breadcrumb('Add Events', "spartian_cp_plugin.php?action=add_event");
			
			//See if anything was submitted.
			
			$post_report = '';
			
			if(isset($mybb->input['users']))
			{
				$usersIDs = $mybb->input['users'];
				
				foreach($usersIDs as $userID){
					$query = array("eventID" => $mybb->input['eventID'], "userId" => $userID);
		
					if($db->insert_query("zcombat_participants", $query))
					{
						$post_report = "Members Add!<br />";
					}
					else
					{
						$post_report = "Your attempt to add a member was a big fail.<br />";
					}
				}
			}
			
			//Make A List Of Events
			
			$query = $db->write_query("SELECT * FROM `mybb_zcombat_events` WHERE `status` = 1 ORDER BY `date` ASC");
			$formDropDownEvents = '<select name="eventID">';

			while($cacheJSON = $db->fetch_array($query)) {
				$cache = json_decode($cacheJSON['cache'], true);
				
				$formDropDownEvents .= "<option value=\"{$cacheJSON['id']}\">{$cache['title']}</option>";
				
			}
			
			$formDropDownEvents .= '</select>';
			
			//Make A Member List For LOL
			
			$query = $db->write_query("SELECT * FROM `mybb_users` ORDER BY `uid` ASC");
			$formUsersChecklist = '';
			$int = 0;

			while($result = $db->fetch_array($query)) {
				$int++;
				if($int == 1)
				{
					$formUsersChecklist .= "<tr>";
				}
				
				$formUsersChecklist .= "<td class=\"trow1\" align=\"left\" valign=\"top\"><input type=\"checkbox\" name=\"users[]\" value=\"{$result['uid']}\">{$result['username']}</td>";
				
				if($int == 4)
				{
					$formUsersChecklist .= "</tr>";
					$int = 0;
				}
			}
			
		$form .= "{$post_report}<form action=\"spartian_cp_plugin.php?action=add_member_to_event\" method=\"POST\">
			
			<table border=\"0\" cellspacing=\"0\" width=\"100%\" cellpadding=\"5\" class=\"tborder\">
				<thead><tr>
					<td class=\"thead\" colspan=\"7\">
						<div><strong><img src=\"skills/overall.gif\">Add Member</a></strong></div>
					</td>
				</thead></tr>
				
				<tbody>
						<tr><td class=\"tcat\" align=\"left\" valign=\"top\" colspan=\"4\">Select Event</td></tr>
						<tr><td class=\"trow1\" align=\"left\" valign=\"top\" colspan=\"4\">{$formDropDownEvents}</td></tr>
						{$formUsersChecklist}
						<tr><td class=\"trow1\" align=\"left\" valign=\"top\" colspan=\"4\"><input type=\"submit\" name=\"add_member\" value=\"Add Member\"></td></tr>

				</tbody>
			</table></form>";
		}
	
	
	break;
	
	}
}

	
$display = "
<table width=\"100%\" border=\"0\" align=\"center\">
	<tbody>
		<tr>
			<td width=\"180\" valign=\"top\">
				<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" class=\"tborder\">
					<tbody>
						<tr>
							<td class=\"thead\"><strong>Menu</strong></td>
						</tr>
						<tr>
							<td class=\"trow1 smalltext\"><a class=\"usercp_nav_item usercp_nav_home\" href=\"spartian_cp_plugin.php\">Upcoming Events</a></td>
						</tr>
						<tr>
							<td class=\"trow1 smalltext\"><a class=\"usercp_nav_item usercp_nav_home\" href=\"spartian_cp_plugin.php?action=participating\">Participating</a></td>
						</tr>
						<tr>
							<td class=\"trow1 smalltext\"><a class=\"usercp_nav_item usercp_nav_home\" href=\"spartian_cp_plugin.php?action=past_events\">Past Events</a></td>
						</tr>
						{$modMenu}
					</tbody>
				</table>
			</td>
			<td valign=\"top\">
			{$form}
			</td>
		</tr>
	</tbody>
</table>
";


eval('$index = "'.$templates->get('chs').'";');
output_page($index);