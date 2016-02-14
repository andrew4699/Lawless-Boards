<?php

	require_once("configuration/mysql.php");

	if(!$_GET['displayname'])
	{
		exit;
	}

	$mQuery = $mysql->query("SELECT `id`, `usertitle`, `avatar` FROM `accounts` WHERE `displayname` = '" . escape($_GET['displayname']) . "'");

	if($mQuery->num_rows)
	{
		$mData = $mQuery->fetch_assoc();

		if(!$mData['usertitle'])
		{
			$userTitleQuery = $mysql->query("SELECT `usergroup` FROM `usergroup_tracker` WHERE `user` = '" . $mData['id'] . "' ORDER BY `primary` DESC");

			if($userTitleQuery->num_rows)
			{
				while($userTitleData = $userTitleQuery->fetch_assoc())
				{
					$userGroupQuery = $mysql->query("SELECT `title` FROM `usergroups` WHERE `id` = '" . $userTitleData['usergroup'] . "'");
					$userGroupData = $userGroupQuery->fetch_assoc();

					if($mData['usertitle'] && $userGropuData['title'])
					{
						$mData['usertitle'] .= "<br>";
					}

					$mData['usertitle'] .= $userGroupData['title'];
				}
			}
		}

		echo json_encode(array("id" => $mData['id'], "title" => $mData['usertitle'], "avatar" => $mData['avatar']));
	}
	else
	{
		echo json_encode(array("id" => 0, "title" => "Invalid user specified", "avatar" => "images/defaultavatar.jpg"));
	}

?>