<?php

	ob_start();

?>

<!DOCTYPE html>

<html lang='en'>
	<head>
		<meta charset='UTF-8'>

		<meta name='keywords' content='Law,Less,Lawless,Boards,Forums,Discussion,People,Post,Comment,User,Control,Panel,Dashboard,Dash,Board,Person,Thread,Category,Section'>
		<meta name='description' content='The new and revolutionary way of creating your forums.'>
		<meta name='author' content='Ricky Phelps'>

		<link rel='Shortcut Icon' href='images/favicon.png'>

		<link rel='stylesheet' href='css/global.css'>

		<link rel='stylesheet' href='css/font-awesome.css'>
		<link rel='stylesheet' href='css/fonts/segoeui/segoeui.css'>
		<link rel='stylesheet' href='css/fonts/segoeuisl/segoeuisl.css'>
		<link rel='stylesheet' href='css/fonts/lato/stylesheet.css'>

		<link rel='stylesheet' href='css/navigation.css'>
		<link rel='stylesheet' href='css/login.css'>
		<link rel='stylesheet' href='css/category.css'>
		<link rel='stylesheet' href='css/section.css'>
		<link rel='stylesheet' href='css/comments.css'>
		<link rel='stylesheet' href='css/captcha.css'>
		<link rel='stylesheet' href='css/image.css'>
		<link rel='stylesheet' href='css/attachments.css'>
		<link rel='stylesheet' href='css/user.css'>
		<link rel='stylesheet' href='css/settings.css'>

		<script src='js/jquery.js'></script>
		<script src='js/ui.js'></script>

		<script src='js/rangyinputs_jquery.js'></script>

		<script src='js/jquery.alerts.js'></script>
		<link rel='stylesheet' href='js/jquery.alerts.css'></script>

		<script src='js/jquery.hovercard.js'></script>
	</head>

	<body ontouchstart=''>
		<div id='wrapper'>
			<?php

				define("UNREGISTERED_GROUP", 2);
				define("REGISTERED_GROUP", 3);
				define("ATTACHMENTS_PATH", "C:/xampp");

				require_once("mysql.php");

				function redirect($page, $time = 0)
				{
					echo "<meta http-equiv='Refresh' content='$time; url=$page'>";
					exit;
				}

				function password($text)
				{
					return hash("whirlpool", $text);
				}

				function cookie($name, $value)
				{
					return setcookie($name, $value, time() + 2592000);
				}

				function getCurrentPage()
				{
					return "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				}

				function email($to, $subject, $message)
				{
					$headers .= "From: Lawless Boards <donotreply@lawlessboards.com>";

					mail($to, $subject, $message, $headers);
				}

				function setPageInfo($pageTitle, $pageDescription)
				{
					if(!$pageDescription)
					{
						$pageDescription = "&nbsp;";
					}

					echo
					"<title>$pageTitle</title>

					<div class='pageTitle'>
						$pageTitle
					</div>

					<div class='pageDescription'>
						$pageDescription
					</div>

					<br>";
				}

				function customDate($date)
				{
					$timeDifference = time() - $date;

					if($timeDifference <= (86400 * 5))
					{
						if($timeDifference <= 86400)
						{
							$currentDate = "Today";
						}
						else if($timeDifference <= (86400 * 2))
						{
							$currentDate = "Yesterday";
						}
						else
						{
							$daysAgo = round($timeDifference / 86400);
							$currentDate = "$daysAgo days ago";
						}
					}
					else
					{
						$currentDate = date("m/d/Y", $date);
					}

					$currentDate .= " at " . date("g:i A", $date);

					return $currentDate;
				}

				function setPageNavigation($type, $id)
				{
					global $mysql;

					switch($type)
					{
						case "index":
						{
							echo "<a href='index'> Lawless Boards</a>";
							break;
						}

						case "category":
						{
							$mQuery = $mysql->query("SELECT `name` FROM `categories` WHERE `id` = '" . escape($id) . "'");
							$mData = $mQuery->fetch_assoc();

							echo "<a href='index'> Lawless Boards</a>  <a href='category?id=$id'>" . $mData['name'] . "</a>";
							break;
						}

						case "section":
						{
							$mQuery = $mysql->query("SELECT `name`, `category`, `parent` FROM `sections` WHERE `id` = '" . escape($id) . "'");
							$mData = $mQuery->fetch_assoc();
							$categoryID = $mData['category'];
							$sectionName = $mData['name'];

							while(!is_null($mData['parent']))
							{
								$oldParent = $mData['parent'];

								$mQuery = $mysql->query("SELECT `name`, `category`, `parent` FROM `sections` WHERE `id` = '$oldParent'");
								$mData = $mQuery->fetch_assoc();

								$parentSections['name' . $oldParent . ''] = $mData['name'];
								$parentSections['section' . $oldParent . ''] = $oldParent;
								$parentSections['category' . $oldParent . ''] = $mData['category'];
							}

							$mQuery = $mysql->query("SELECT `name` FROM `categories` WHERE `id` = '$categoryID'");
							$mData = $mQuery->fetch_assoc();

							echo "<a href='index'> Lawless Boards</a>  <a href='category?id=$categoryID'>" . $mData['name'] . "</a> ";

							foreach(array_reverse($parentSections) as $arrayIndex => $arrayValue)
							{
								if(strpos($arrayIndex, "section") !== false)
								{
									echo " <a href='section?id=$arrayValue'>";
								}
								else if(strpos($arrayIndex, "name") !== false)
								{
									echo "$arrayValue</a> ";
								}
							}

							echo " <a href='section?id=$id'>$sectionName</a>";
							break;
						}

						case "thread":
						{
							$mQuery = $mysql->query("SELECT `title`, `section` FROM `threads` WHERE `id` = '" . escape($id) . "'");
							$mData = $mQuery->fetch_assoc();
							$sectionID = $mData['section'];
							$threadTitle = $mData['title'];

							$mQuery = $mysql->query("SELECT `name`, `category` FROM `sections` WHERE `id` = '$sectionID'");
							$mData = $mQuery->fetch_assoc();
							$categoryID = $mData['category'];
							$sectionName = $mData['name'];

							$mQuery = $mysql->query("SELECT `name` FROM `categories` WHERE `id` = '$categoryID'");
							$mData = $mQuery->fetch_assoc();

							echo "<a href='index'> Lawless Boards</a>  <a href='category?id=$categoryID'>" . $mData['name'] . "</a>";

							$sectionQuery = $mysql->query("SELECT `name`, `category`, `parent` FROM `sections` WHERE `id` = '" . escape($sectionID) . "'");
							$sectionData = $sectionQuery->fetch_assoc();
							$categoryID = $sectionData['category'];
							$sectionName = $sectionData['name'];

							while(!is_null($sectionData['parent']))
							{
								$oldParent = $sectionData['parent'];

								$sectionQuery = $mysql->query("SELECT `name`, `category`, `parent` FROM `sections` WHERE `id` = '$oldParent'");
								$sectionData = $sectionQuery->fetch_assoc();

								$parentSections['name' . $oldParent . ''] = $sectionData['name'];
								$parentSections['section' . $oldParent . ''] = $oldParent;
								$parentSections['category' . $oldParent . ''] = $sectionData['category'];
							}

							foreach(array_reverse($parentSections) as $arrayIndex => $arrayValue)
							{
								if(strpos($arrayIndex, "section") !== false)
								{
									echo " <a href='section?id=$arrayValue'>";
								}
								else if(strpos($arrayIndex, "name") !== false)
								{
									echo "$arrayValue</a> ";
								}
							}

							echo "  <a href='section?id=$sectionID'>$sectionName</a>  <a href='thread?id=$id'>$threadTitle</a>";
							break;
						}
					}

					echo "<br><br>";
				}

				function userNameTags($accountID = -1, $username)
				{
					global $mysql;

					if($accountID == -1 && $_SESSION['accountid'])
					{
						$accountID = $_SESSION['accountid'];
					}

					$mQuery = $mysql->query("SELECT `usergroup` FROM `usergroup_tracker` WHERE `user` = '$accountID' AND `primary` = '1'");
					$mData = $mQuery->fetch_assoc();

					$mQuery = $mysql->query("SELECT `nametag`, `nametagclose` FROM `usergroups` WHERE `id` = '" . $mData['usergroup'] . "'");
					$mData = $mQuery->fetch_assoc();

					return $mData['nametag'].$username.$mData['nametagclose'];
				}

				function addUserGroup($accountID, $userGroupID, $primaryGroup = 0)
				{
					global $mysql;

					if($primaryGroup)
					{
						$mysql->query("UPDATE `usergroup_tracker` SET `primary` = '0' WHERE `user` = '$accountID'");
					}

					$mysql->query("SELECT `id` FROM `usergroup_tracker` WHERE `user` = '$accountID' AND `usergroup` = '$userGroupID'");

					if(!$mysql->num_rows)
					{
						$mysql->query("INSERT INTO `usergroup_tracker` (`user`, `usergroup`, `primary`) VALUES ('$accountID', '$userGroupID', '$primaryGroup')");
					}
				}

				function getPostShadow($accountID)
				{
					global $mysql;

					$mQuery = $mysql->query("SELECT `usergroup` FROM `usergroup_tracker` WHERE `user` = '$accountID' AND `primary` = '1'");
					$mData = $mQuery->fetch_assoc();

					$mQuery = $mysql->query("SELECT `postshadow` FROM `usergroups` WHERE `id` = '" . $mData['usergroup'] . "'");
					$mData = $mQuery->fetch_assoc();

					return ($mData['postshadow']) ? $mData['postshadow'] : "transparent";
				}

				function getParentIndex($parentIndex)
				{
					for($integerIndex; $integerIndex < $parentIndex; $integerIndex++)
					{
						$parentIndexString .= "&nbsp;&nbsp;&nbsp;";
					}

					return $parentIndexString;
				}

				function removeDirectory($directoryPath)
				{
				    if(!file_exists($directoryPath)) return true;
				    if(!is_dir($directoryPath)) return unlink($directoryPath);

				    foreach(scandir($directoryPath) as $item)
				    {
				        if ($item == '.' || $item == '..') continue;
				        if (!removeDirectory($directoryPath.DIRECTORY_SEPARATOR.$item)) return false;
				    }

				    return rmdir($directoryPath);
				}

				function getPostCount($accountID)
				{
					global $mysql;

					$totalPosts = 0;

					$mQuery = $mysql->query("SELECT `id` FROM `threads` WHERE `poster` = '$accountID'");

					$totalPosts += $mQuery->num_rows;

					$mQuery = $mysql->query("SELECT `id` FROM `comments` WHERE `poster` = '$accountID'");

					$totalPosts += $mQuery->num_rows;

					return $totalPosts;
				}

				$temporaryPermissions = array();

				function storePermissions($accountID)
				{
					global $mysql, $temporaryPermissions;

					$mQuery = $mysql->query("SELECT * FROM `usergroup_tracker` WHERE `user` = '$accountID'");

					if($mQuery->num_rows)
					{
						while($mData = $mQuery->fetch_assoc())
						{
							$userGroupQuery = $mysql->query("SELECT * FROM `usergroups` WHERE `id` = '" . $mData['usergroup'] . "'");
							$userGroupData = $userGroupQuery->fetch_assoc();

							foreach($userGroupData as $arrayIndex => $arrayValue)
							{
								if($arrayValue > $temporaryPermissions[$arrayIndex])
								{
									$temporaryPermissions[$arrayIndex] = ($arrayValue) ? $arrayValue : 0;
								}
							}

							if($userGroupData['staff'])
							{
								$staffQuery = $mysql->query("SELECT * FROM `staffpermissions` WHERE `id` = '" . $userGroupData['staff'] . "'");
								$staffData = $staffQuery->fetch_assoc();

								foreach($staffData as $arrayIndex => $arrayValue)
								{
									if($arrayValue > $temporaryPermissions[$arrayIndex])
									{
										$temporaryPermissions[$arrayIndex] = ($arrayValue) ? $arrayValue : 0;
									}
								}
							}
						}
					}
				}

				function getAvatarStyle($accountID)
				{
					global $mysql;

					storePermissions($accountID);

					global $temporaryPermissions;

					return "style='max-width: " . $temporaryPermissions['maxavatarwidth'] . "px; max-height: " . $temporaryPermissions['maxavatarheight'] . "px;'";
				}

				$timezoneList = array
				(
	                '-12'  => 'Pacific/Kwajalein',   '-11'  => 'Pacific/Samoa',
	                '-10'  => 'Pacific/Honolulu',    '-9'   => 'America/Juneau',
	                '-8'   => 'America/Los_Angeles', '-7'   => 'America/Denver',
	                '-6'   => 'America/Mexico_City', '-5'   => 'America/New_York',  
	                '-4'   => 'America/Caracas',     '-3.5' => 'America/St_Johns',
	                '-3'   => 'America/Argentina/Buenos_Aires',
	                '-2'   => 'Atlantic/Azores',     '-1'   => 'Atlantic/Azores',
	                '0'    => 'Europe/London',       '1'    => 'Europe/Paris',
	                '2'    => 'Europe/Helsinki',     '3'    => 'Europe/Moscow',
	                '3.5'  => 'Asia/Tehran',         '4'    => 'Asia/Baku',
	                '4.5'  => 'Asia/Kabul',          '5'    => 'Asia/Karachi',
	                '5.5'  => 'Asia/Calcutta',       '6'    => 'Asia/Colombo',
	                '7'    => 'Asia/Bangkok',        '8'    => 'Asia/Singapore',
	                '9'    => 'Asia/Tokyo',          '9.5'  => 'Australia/Darwin',
	                '10'   => 'Pacific/Guam',        '11'   => 'Asia/Magadan',
	                '12'   => 'Asia/Kamchatka'
	            );

				require_once("bbcode/Parser.php");

				require_once("includes/navigation.php");

				if(!$_SESSION['accountid'])
				{
					if($_COOKIE['lb_accountid'] && $_COOKIE['lb_username'] && $_COOKIE['lb_password'])
					{
						$mQuery = $mysql->query("SELECT `id` FROM `accounts` WHERE `id` = '" . escape($_COOKIE['lb_accountid']) . "' AND `username` = '" . escape($_COOKIE['lb_username']) . "' AND `password` = '" . escape($_COOKIE['lb_password']) . "'");

						if($mQuery->num_rows)
						{
							$mData = $mQuery->fetch_assoc();
							$_SESSION['accountid'] = $mData['id'];
							redirect("");
						}
					}

					$mQuery = $mysql->query("SELECT * FROM `usergroups` WHERE `id` = '" . UNREGISTERED_GROUP . "'");
					$permissions = $mQuery->fetch_assoc();

					if($permissions['staff'])
					{
						$mQuery = $mysql->query("SELECT * FROM `staffpermissions` WHERE `id` = '" . $permissions['staff'] . "'");
						$mData = $mQuery->fetch_assoc();

						foreach($mData as $arrayIndex => $arrayValue)
						{
							$permissions[$arrayIndex] = $arrayValue;
						}
					}

					echo
					"<div id='loginNotice' align='center' class='loginNotice'>
						<form action='login' method='POST' autocomplete='off'>
							<input type='text' name='username' placeholder=' Username' maxlength='50' class='loginInput' required>
							<input type='password' name='password' placeholder=' Password' class='loginInput' required>
							<input type='submit' name='login' value='Login' class='loginButton'>
						</form>
					</div>";

					date_default_timezone_set("America/Los Angeles");
				}
				else
				{
					$mQuery = $mysql->query("SELECT * FROM `accounts` WHERE `id` = '" . $_SESSION['accountid'] . "'");
					$user = $mQuery->fetch_assoc();

					$mQuery = $mysql->query("SELECT * FROM `usergroup_tracker` WHERE `user` = '" . $_SESSION['accountid'] . "'");

					if($mQuery->num_rows)
					{
						while($mData = $mQuery->fetch_assoc())
						{
							$userGroupQuery = $mysql->query("SELECT * FROM `usergroups` WHERE `id` = '" . $mData['usergroup'] . "'");
							$userGroupData = $userGroupQuery->fetch_assoc();

							foreach($userGroupData as $arrayIndex => $arrayValue)
							{
								if($arrayValue > $permissions[$arrayIndex])
								{
									$permissions[$arrayIndex] = ($arrayValue) ? $arrayValue : 0;
								}
							}

							if($userGroupData['staff'])
							{
								$staffQuery = $mysql->query("SELECT * FROM `staffpermissions` WHERE `id` = '" . $userGroupData['staff'] . "'");
								$staffData = $staffQuery->fetch_assoc();

								foreach($staffData as $arrayIndex => $arrayValue)
								{
									if($arrayValue > $permissions[$arrayIndex])
									{
										$permissions[$arrayIndex] = ($arrayValue) ? $arrayValue : 0;
									}
								}
							}
						}
					}

					$userAvatarStyle = "style='max-width: " . $permissions['maxavatarwidth'] . "px; max-height: " . $permissions['maxavatarheight'] . "px;'";

					date_default_timezone_set($timezoneList[(strpos($user['timezone'], ".0")) ? round($user['timezone']) : $user['timezone']]);

					$mysql->query("UPDATE `accounts` SET `ip` = '" . $_SERVER['REMOTE_ADDR'] . "', `lastactivity` = '" . time() . "' WHERE `id` = '" . $_SESSION['accountid'] . "'");
				}

				$bbParser = new JBBCode\Parser();
				$bbParser->loadDefaultCodes();

				$bbParser->addBBCode("quote", "<div class='quote'>{param}</div> <br>");
				$bbParser->addBBCode("left", "<div align='left'>{param}</div>");
				$bbParser->addBBCode("center", "<div align='center'>{param}</div>");
				$bbParser->addBBCode("right", "<div align='right'>{param}</div>");
				$bbParser->addBBCode("email", "<a href='mailto:{param}'>{param}</a>");
				$bbParser->addBBCode("font", "<span style='font-family: {option};'>{param}</span>", true);
				$bbParser->addBBCode("size", "<span style='font-size: {option}px;'>{param}</span>", true);
				$bbParser->addBBCode("list", "<ul>{param}</ul>");
				$bbParser->addBBCode("nlist", "<ol>{param}</ol>");
				$bbParser->addBBCode("li", "<li>{param}</li>");
				$bbParser->addBBCode("mention", "<span data-user='{param}' class='mentionUser bold'>{param}</span>");

			?>

			<script>
				$(document).ready(function()
				{
					$('*').hover(function(event)
					{
						var elementTooltip = $(this).data("tooltip");

						if(elementTooltip && !$('.imageCover').is(":visible"))
						{
							$('#tooltip').stop().remove();

							$('#wrapper').append(
							"<div id='tooltip' class='tooltip'> \
								" + elementTooltip + " \
							</div>");

							$('#tooltip').css({top: event.pageY + 25, left: event.pageX + 5}).hide().fadeIn(150);

							$(document).mousemove(function(event)
							{
								$('#tooltip').offset({top: event.pageY + 25, left: event.pageX + 5});
							});
						}
					},
					function()
					{
						if($(this).data("tooltip"))
						{
							$('#tooltip').fadeOut(150, function()
							{
								$(this).remove();
							});

							$(this).unbind("mousemove");
						}
					});

					$('*').click(function()
					{
						var elementWarning = $(this).data("warning");

						if(elementWarning)
						{
							var elementHref = $(this).attr("href");

							event.preventDefault();

							jConfirm(elementWarning, $(this).data("warningtitle"), function(isConfirmed)
							{
								if(isConfirmed)
								{
									window.location.replace(elementHref);
								}
							});
						}
					});

					$('img').each(function()
					{
						if(!$(this).data("noenlarge"))
						{
							$(this).data("tooltip", "Click to enlarge").addClass("zoomImage");
						}
					});

					$('img').click(function()
					{
						var currentElement = this;

						if(!$('.imageCover').is(":visible") && !$(this).data("noenlarge"))
						{
							$('#wrapper').append(
							"<div align='center' class='imageCover hidden'> \
								<div class='imageContainer'> \
								</div> \
							</div>");

							$('.imageCover').click(function()
							{
								$(this).fadeOut(500, function()
								{
									$(this).remove();
								});
							});

							$(currentElement).clone().appendTo('.imageContainer');

							$('.imageCover').fadeIn(500, function()
							{
								$('.imageContainer').animate({width: $('.imageContainer img').width(), height: $('.imageContainer img').height()}, 500);
							});
						}
					});
				});
			</script>