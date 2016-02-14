<?php

	require_once("configuration/main.php");

	if(!$_SESSION['accountid'])
	{
		redirect("index");
	}

	setPageInfo("Settings", "");

?>

<table class='tdAlignTop'>
	<tr>
		<td width='200'>
			<div class='settingsNavigationHeader'>
				Messages
			</div>

			<div class='settingsNavigationBox'>
				<a href='?view=inbox'>
					Inbox
				</a>

				<a href='?view=send'>
					Send Private Message
				</a>
			</div>

			<div class='settingsNavigationHeader'>
				General Settings
			</div>

			<div class='settingsNavigationBox'>
				<a href='?view=profile'>
					Edit Profile
				</a>

				<?php if($permissions['uploadavatar']) { ?>

				<a href='?view=avatar'>
					Change Avatar
				</a>

				<?php } ?>

				<?php if($permissions['allowsignature']) { ?>

				<a href='?view=signature'>
					Modify Signature
				</a>

				<?php } ?>

				<?php if($permissions['hiddenmode']) { ?>

				<a href='?view=hidden&from=<?php echo $_GET['view']; ?>' data-tooltip='When hidden mode is on, you will not appear as online to other users.'>
					Hidden Mode: <span class='bold'><?php echo ($user['hidden']) ? "On" : "Off"; ?></span>
				</a>

				<?php } ?>
			</div>
		</td>

		<td width='20'></td>

		<td width='900'>
			<?php

				switch($_GET['view'])
				{
					case "inbox":
					{
						echo
						"<div class='categoryTitle'>
							Private Messages
						</div>";

						$mQuery = $mysql->query("SELECT * FROM `privatemessages` WHERE `to` = '" . $_SESSION['accountid'] . "' ORDER BY `date` DESC");

						while($mData = $mQuery->fetch_assoc())
						{
							$tooltip = ($mData['read']) ? "You have read this message." : "You have not read this message.";

							$accountQuery = $mysql->query("SELECT `displayname` FROM `accounts` WHERE `id` = '" . $mData['from'] . "'");
							$accountData = $accountQuery->fetch_assoc();

							echo
							"<a href='viewmessage?id=" . $mData['id'] . "' data-tooltip='$tooltip'>
								<div class='sectionContainer'>
									<table>
										<tr>
											<td>
												<div class='sectionIcon'>
													
												</div>
											</td>

											<td>
												<div class='sectionName'>
													" . $mData['title'] . "
												</div>

												<div class='sectionDescription'>
													Sent by " . $accountData['displayname'] . " - " . customDate($mData['date']) . "
												</div>
											</td>
										</tr>
									</table>
								</div>
							</a>";
						}

						break;
					}

					case "send":
					{
						if($_POST['sendmessage'])
						{
							$mQuery = $mysql->query("SELECT `id` FROM `accounts` WHERE `displayname` = '" . escape($_POST['to']) . "'");

							if($mQuery->num_rows)
							{
								$mData = $mQuery->fetch_assoc();

								if(strlen($_POST['title']) >= 3)
								{
									if(strlen($_POST['message']) >= 3)
									{
										if(time() - $_SESSION['lastcomment'] >= 60)
										{
											$mysql->query("INSERT INTO `privatemessages` (`to`, `from`, `date`, `title`, `message`) VALUES ('" . $mData['id'] . "', '" . $_SESSION['accountid'] . "', '" . time() . "', '" . escape($_POST['title']) . "', '" . escape($_POST['message']) . "')");

											$_SESSION['lastcomment'] = time();

											echo "Your message has been sent.";
											redirect("settings?view=profile", 2);
										}
										else
										{
											$timeBeforeComment = 60 - (time() - $_SESSION['lastcomment']);

											echo
											"<div class='box'>
												<div class='boxHeading'>
													You must wait $timeBeforeComment seconds before posting another thread, comment, profile message, or private message.
												</div>
											</div>

											<br>";
										}
									}
									else
									{
										echo
										"<div class='box'>
											<div class='boxHeading'>
												Your message must be at least 3 characters long.
											</div>
										</div>

										<br>";
									}
								}
								else
								{
									echo
									"<div class='box'>
										<div class='boxHeading'>
											Your title must be at least 3 characters long.
										</div>
									</div>

									<br>";
								}
							}
							else
							{
								echo
								"<div class='box'>
									<div class='boxHeading'>
										You have specified an invalid user to send the message to.
									</div>
								</div>

								<br>";
							}
						}

						echo "<datalist id='userList'>";

						$userQuery = $mysql->query("SELECT `displayname` FROM `accounts`");

						while($userData = $userQuery->fetch_assoc())
						{
							echo "<option value='" . $userData['displayname'] . "'>";
						}

						echo
						"</datalist>

						<form action='' method='POST'>
							<div class='box'>
								<div class='boxHeading'>
									Send Private Message
								</div>

								<div class='boxMain'>
									<div class='boxArea'>
										<table>
											<tr>
												<td width='100%'>To:</td>
												<td><input type='text' name='to' placeholder='To' value='" . $_POST['to'] . "' list='userList' maxlength='50' class='boxFormInput' autocomplete='off' autofocus required></td>
											</tr>
										</table>
									</div>

									<div class='boxArea'>
										<table>
											<tr>
												<td width='100%'>Title:</td>
												<td><input type='text' name='title' placeholder='Title' value='" . $_POST['title'] . "' maxlength='200' class='boxFormInput' required></td>
											</tr>
										</table>
									</div>

									<div class='boxArea'>
										<button type='button' data-tag='B' class='bbcode boxButton'>bold</button>
										<button type='button' data-tag='I' class='bbcode boxButton'>italic</button>
										<button type='button' data-tag='U' class='bbcode boxButton'>underline</button>
										<button type='button' data-tag='LEFT' class='bbcode boxButton'>left</button>
										<button type='button' data-tag='CENTER' class='bbcode boxButton'>center</button>
										<button type='button' data-tag='RIGHT' class='bbcode boxButton'>right</button>
										<button type='button' data-tag='LIST' class='bbcode boxButton'>bullet list</button>
										<button type='button' data-tag='NLIST' class='bbcode boxButton'>number list</button>
										<button type='button' data-tag='LI' class='bbcode boxButton'>list item</button>
										<button type='button' data-tag='EMAIL' class='bbcode boxButton'>email</button>
										<button type='button' data-tag='IMG' class='bbcode boxButton'>image</button>
										<button type='button' data-tag='QUOTE' class='bbcode boxButton'>quote</button>
										<button id='bbcode-link' type='button' data-tooltip='Example: [URL=http://example.com]Click here[/URL]' class='boxButton'>link</button>
										<button id='bbcode-font' type='button' data-tooltip='Example: [FONT=Arial]Hello world![/FONT]' class='boxButton'>font</button>
										<button id='bbcode-size' type='button' data-tooltip='Example: [SIZE=5]Hello world![/SIZE]' class='boxButton'>size</button>
										<button id='bbcode-color' type='button' data-tooltip='Example: [COLOR=RED]Hello[/COLOR] [COLOR=#00FF00]world![/COLOR]' class='boxButton'>color</button> ";

										if($permissions['mentionusers'])
										{
											echo "<button type='button' data-tag='MENTION' data-tooltip='Example: [MENTION]Jimmy[/MENTION]' class='bbcode boxButton'>mention</button>";
										}

										echo
										"<br><br>

										<textarea id='message' name='message' placeholder=' Message' maxlength='15000' class='boxTextArea' required>" . $_POST['message'] . "</textarea>
									</div>
								</div>
							</div>

							<div align='right'>
								<input type='submit' name='sendmessage' value='Send Message' class='boxButton'>
							</div>
						</form>";

						break;
					}

					case "profile":
					{
						if($_POST['changeusername'])
						{
							if(strlen($_POST['username']) >= 2)
							{
								if($_POST['username'] != $user['username'])
								{
									$mQuery = $mysql->query("SELECT `id` FROM `accounts` WHERE `username` = '" . escape($_POST['username']) . "'");

									if(!$mQuery->num_rows)
									{
										$mysql->query("UPDATE `accounts` SET `username` = '" . escape($_POST['username']) . "' WHERE `id` = '" . $_SESSION['accountid'] . "'");

										if($_COOKIE['lb_username'])
										{
											cookie("lb_username", $_POST['username']);
										}

										echo "Your user name has been changed.";
										redirect("", 2);
									}
									else echo "That username is already taken. <br> <br>";
								}
								else echo "Your new username must be different than your current one. <br> <br>";
							}
							else echo "Your username must be at least 2 characters long. <br> <br>";
						}
						else if($_POST['changepassword'])
						{
							if(strlen($_POST['newpassword']) >= 4)
							{
								if($_POST['newpassword'] == $_POST['confirmnewpassword'])
								{
									if($_POST['currentpassword'] != $_POST['newpassword'])
									{
										if($user['password'] == password($_POST['currentpassword']))
										{
											$mysql->query("UPDATE `accounts` SET `password` = '" . password($_POST['newpassword']) . "' WHERE `id` = '" . $_SESSION['accountid'] . "'");

											echo "Your password has been changed.";
											redirect("", 2);
										}
										else echo "You have entered your current password incorrectly. <br> <br>";
									}
									else echo "Your new password must be different than your current one. <br> <br>";
								}
								else echo "Your new password must match the confirmed new password field. <br> <br>";
							}
							else echo "Your new password must be at least 4 characters long. <br> <br>";
						}
						else if($_POST['changeemail'])
						{
							if($user['email'] != $_POST['email'])
							{
								if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && strpos($_POST['email'], "."))
								{
									$mQuery = $mysql->query("SELECT `id` FROM `accounts` WHERE `email` = '" . escape($_POST['email']) . "'");

									if(!$mQuery->num_rows)
									{
										$mysql->query("UPDATE `accounts` SET `email` = '" . escape($_POST['email']) . "' WHERE `id` = '" . $_SESSION['accountid'] . "'");

										echo "Your email address has been changed.";
										redirect("", 2);
									}
									else echo "That email address is already taken. <br> <br>";
								}
								else echo "You have entered an invalid email address. <br> <br>";
							}
							else echo "Your new email address must be different than your current one. <br> <br>";
						}
						else if($_POST['changebirthday'])
						{
							if($user['birthday'] != $_POST['birthday'])
							{
								$mysql->query("UPDATE `accounts` SET `birthday` = '" . escape($_POST['birthday']) . "' WHERE `id` = '" . $_SESSION['accountid'] . "'");

								echo "Your birthday has been changed.";
								redirect("", 2);
							}
							else echo "Your new birthday must be different than your current one. <br> <br>";
						}
						else if($_POST['changecountry'])
						{
							if($user['country'] != $_POST['country'])
							{
								$mysql->query("UPDATE `accounts` SET `country` = '" . escape($_POST['country']) . "' WHERE `id` = '" . $_SESSION['accountid'] . "'");

								echo "Your country has been changed.";
								redirect("", 2);
							}
							else echo "Your new country must be different than your current one. <br> <br>";
						}
						else if($_POST['changetimezone'])
						{
							if($user['timezone'] != $_POST['timezone'])
							{
								$mysql->query("UPDATE `accounts` SET `timezone` = '" . escape($_POST['timezone']) . "' WHERE `id` = '" . $_SESSION['accountid'] . "'");

								echo "Your timezone has been changed.";
								redirect("", 2);
							}
							else echo "Your new timezone must be different than your current one. <br> <br>";
						}
						else if($_POST['changeusertitle'] && $permissions['setowntitle'])
						{
							if($user['usertitle'] != $_POST['usertitle'])
							{
								$mysql->query("UPDATE `accounts` SET `usertitle` = '" . escape($_POST['usertitle']) . "' WHERE `id` = '" . $_SESSION['accountid'] . "'");

								echo "Your user title has been changed.";
								redirect("", 2);
							}
							else echo "Your new user title must be different than your current one. <br> <br>";
						}

						echo
						"<div class='box'>
							<div class='boxHeading'>
								Edit Profile
							</div>

							<div class='boxMain'>
								<div class='boxArea'>
									<form action='' method='POST'>
										<table>
											<tr>
												<td width='200'>User Name:</td>
												<td><input type='text' name='username' placeholder='User Name' value='" . $user['username'] . "' class='boxFormInput'> <input type='submit' name='changeusername' value='Change Username' class='boxButton'></td>
											</tr>
										</table>
									</form>
								</div>

								<div class='boxArea'>
									<form action='' method='POST'>
										<table>
											<tr>
												<td width='200'>Current Password:</td>
												<td><input type='password' name='currentpassword' placeholder='Current Password' class='boxFormInput'></td>
											</tr>

											<tr>
												<td width='200'>New Password:</td>
												<td><input type='password' name='newpassword' placeholder='New Password' class='boxFormInput'></td>
											</tr>

											<tr>
												<td width='200'>Confirm New Password:</td>
												<td><input type='password' name='confirmnewpassword' placeholder='Confirm New Password' class='boxFormInput'> <input type='submit' name='changepassword' value='Change Password' class='boxButton'></td>
											</tr>
										</table>
									</form>
								</div>

								<div class='boxArea'>
									<form action='' method='POST'>
										<table>
											<tr>
												<td width='200'>Email Address:</td>
												<td><input type='text' name='email' placeholder='Email Address' value='" . $user['email'] . "' class='boxFormInput'> <input type='submit' name='changeemail' value='Change Email Address' class='boxButton'></td>
											</tr>
										</table>
									</form>
								</div>

								<div class='boxArea'>
									<form action='' method='POST'>
										<table>
											<tr>
												<td width='200'>Birthday:</td>
												<td><input type='date' name='birthday' placeholder='Birthday' value='" . $user['birthday'] . "' class='boxFormInput'> <input type='submit' name='changebirthday' value='Change Birthday' class='boxButton'></td>
											</tr>
										</table>
									</form>
								</div>

								<div class='boxArea'>
									<form action='' method='POST'>
										<table>
											<tr>
												<td width='200'>Country:</td>
												<td>
													<select id='country' name='country' class='boxFormInput'>
														<option value='Afghanistan'>Afghanistan</option>
														<option value='Åland Islands'>Åland Islands</option>
														<option value='Albania'>Albania</option>
														<option value='Algeria'>Algeria</option>
														<option value='American Samoa'>American Samoa</option>
														<option value='Andorra'>Andorra</option>
														<option value='Angola'>Angola</option>
														<option value='Anguilla'>Anguilla</option>
														<option value='Antarctica'>Antarctica</option>
														<option value='Antigua and Barbuda'>Antigua and Barbuda</option>
														<option value='Argentina'>Argentina</option>
														<option value='Armenia'>Armenia</option>
														<option value='Aruba'>Aruba</option>
														<option value='Australia'>Australia</option>
														<option value='Austria'>Austria</option>
														<option value='Azerbaijan'>Azerbaijan</option>
														<option value='Bahamas'>Bahamas</option>
														<option value='Bahrain'>Bahrain</option>
														<option value='Bangladesh'>Bangladesh</option>
														<option value='Barbados'>Barbados</option>
														<option value='Belarus'>Belarus</option>
														<option value='Belgium'>Belgium</option>
														<option value='Belize'>Belize</option>
														<option value='Benin'>Benin</option>
														<option value='Bermuda'>Bermuda</option>
														<option value='Bhutan'>Bhutan</option>
														<option value='Bolivia'>Bolivia</option>
														<option value='Bosnia and Herzegovina'>Bosnia and Herzegovina</option>
														<option value='Botswana'>Botswana</option>
														<option value='Bouvet Island'>Bouvet Island</option>
														<option value='Brazil'>Brazil</option>
														<option value='British Indian Ocean Territory'>British Indian Ocean Territory</option>
														<option value='Brunei Darussalam'>Brunei Darussalam</option>
														<option value='Bulgaria'>Bulgaria</option>
														<option value='Burkina Faso'>Burkina Faso</option>
														<option value='Burundi'>Burundi</option>
														<option value='Cambodia'>Cambodia</option>
														<option value='Cameroon'>Cameroon</option>
														<option value='Canada'>Canada</option>
														<option value='Cape Verde'>Cape Verde</option>
														<option value='Cayman Islands'>Cayman Islands</option>
														<option value='Central African Republic'>Central African Republic</option>
														<option value='Chad'>Chad</option>
														<option value='Chile'>Chile</option>
														<option value='China'>China</option>
														<option value='Christmas Island'>Christmas Island</option>
														<option value='Cocos (Keeling) Islands'>Cocos (Keeling) Islands</option>
														<option value='Colombia'>Colombia</option>
														<option value='Comoros'>Comoros</option>
														<option value='Congo'>Congo</option>
														<option value='Congo, The Democratic Republic of The'>Congo, The Democratic Republic of The</option>
														<option value='Cook Islands'>Cook Islands</option>
														<option value='Costa Rica'>Costa Rica</option>
														<option value='Cote D'ivoire'>Cote D'ivoire</option>
														<option value='Croatia'>Croatia</option>
														<option value='Cuba'>Cuba</option>
														<option value='Cyprus'>Cyprus</option>
														<option value='Czech Republic'>Czech Republic</option>
														<option value='Denmark'>Denmark</option>
														<option value='Djibouti'>Djibouti</option>
														<option value='Dominica'>Dominica</option>
														<option value='Dominican Republic'>Dominican Republic</option>
														<option value='Ecuador'>Ecuador</option>
														<option value='Egypt'>Egypt</option>
														<option value='El Salvador'>El Salvador</option>
														<option value='Equatorial Guinea'>Equatorial Guinea</option>
														<option value='Eritrea'>Eritrea</option>
														<option value='Estonia'>Estonia</option>
														<option value='Ethiopia'>Ethiopia</option>
														<option value='Falkland Islands (Malvinas)'>Falkland Islands (Malvinas)</option>
														<option value='Faroe Islands'>Faroe Islands</option>
														<option value='Fiji'>Fiji</option>
														<option value='Finland'>Finland</option>
														<option value='France'>France</option>
														<option value='French Guiana'>French Guiana</option>
														<option value='French Polynesia'>French Polynesia</option>
														<option value='French Southern Territories'>French Southern Territories</option>
														<option value='Gabon'>Gabon</option>
														<option value='Gambia'>Gambia</option>
														<option value='Georgia'>Georgia</option>
														<option value='Germany'>Germany</option>
														<option value='Ghana'>Ghana</option>
														<option value='Gibraltar'>Gibraltar</option>
														<option value='Greece'>Greece</option>
														<option value='Greenland'>Greenland</option>
														<option value='Grenada'>Grenada</option>
														<option value='Guadeloupe'>Guadeloupe</option>
														<option value='Guam'>Guam</option>
														<option value='Guatemala'>Guatemala</option>
														<option value='Guernsey'>Guernsey</option>
														<option value='Guinea'>Guinea</option>
														<option value='Guinea-bissau'>Guinea-bissau</option>
														<option value='Guyana'>Guyana</option>
														<option value='Haiti'>Haiti</option>
														<option value='Heard Island and Mcdonald Islands'>Heard Island and Mcdonald Islands</option>
														<option value='Holy See (Vatican City State)'>Holy See (Vatican City State)</option>
														<option value='Honduras'>Honduras</option>
														<option value='Hong Kong'>Hong Kong</option>
														<option value='Hungary'>Hungary</option>
														<option value='Iceland'>Iceland</option>
														<option value='India'>India</option>
														<option value='Indonesia'>Indonesia</option>
														<option value='Iran, Islamic Republic of'>Iran, Islamic Republic of</option>
														<option value='Iraq'>Iraq</option>
														<option value='Ireland'>Ireland</option>
														<option value='Isle of Man'>Isle of Man</option>
														<option value='Israel'>Israel</option>
														<option value='Italy'>Italy</option>
														<option value='Jamaica'>Jamaica</option>
														<option value='Japan'>Japan</option>
														<option value='Jersey'>Jersey</option>
														<option value='Jordan'>Jordan</option>
														<option value='Kazakhstan'>Kazakhstan</option>
														<option value='Kenya'>Kenya</option>
														<option value='Kiribati'>Kiribati</option>
														<option value='Korea, Democratic Peoples Republic of'>Korea, Democratic People's Republic of</option>
														<option value='Korea, Republic of'>Korea, Republic of</option>
														<option value='Kuwait'>Kuwait</option>
														<option value='Kyrgyzstan'>Kyrgyzstan</option>
														<option value='Lao People's Democratic Republic'>Lao People's Democratic Republic</option>
														<option value='Latvia'>Latvia</option>
														<option value='Lebanon'>Lebanon</option>
														<option value='Lesotho'>Lesotho</option>
														<option value='Liberia'>Liberia</option>
														<option value='Libyan Arab Jamahiriya'>Libyan Arab Jamahiriya</option>
														<option value='Liechtenstein'>Liechtenstein</option>
														<option value='Lithuania'>Lithuania</option>
														<option value='Luxembourg'>Luxembourg</option>
														<option value='Macao'>Macao</option>
														<option value='Macedonia, The Former Yugoslav Republic of'>Macedonia, The Former Yugoslav Republic of</option>
														<option value='Madagascar'>Madagascar</option>
														<option value='Malawi'>Malawi</option>
														<option value='Malaysia'>Malaysia</option>
														<option value='Maldives'>Maldives</option>
														<option value='Mali'>Mali</option>
														<option value='Malta'>Malta</option>
														<option value='Marshall Islands'>Marshall Islands</option>
														<option value='Martinique'>Martinique</option>
														<option value='Mauritania'>Mauritania</option>
														<option value='Mauritius'>Mauritius</option>
														<option value='Mayotte'>Mayotte</option>
														<option value='Mexico'>Mexico</option>
														<option value='Micronesia, Federated States of'>Micronesia, Federated States of</option>
														<option value='Moldova, Republic of'>Moldova, Republic of</option>
														<option value='Monaco'>Monaco</option>
														<option value='Mongolia'>Mongolia</option>
														<option value='Montenegro'>Montenegro</option>
														<option value='Montserrat'>Montserrat</option>
														<option value='Morocco'>Morocco</option>
														<option value='Mozambique'>Mozambique</option>
														<option value='Myanmar'>Myanmar</option>
														<option value='Namibia'>Namibia</option>
														<option value='Nauru'>Nauru</option>
														<option value='Nepal'>Nepal</option>
														<option value='Netherlands'>Netherlands</option>
														<option value='Netherlands Antilles'>Netherlands Antilles</option>
														<option value='New Caledonia'>New Caledonia</option>
														<option value='New Zealand'>New Zealand</option>
														<option value='Nicaragua'>Nicaragua</option>
														<option value='Niger'>Niger</option>
														<option value='Nigeria'>Nigeria</option>
														<option value='Niue'>Niue</option>
														<option value='Norfolk Island'>Norfolk Island</option>
														<option value='Northern Mariana Islands'>Northern Mariana Islands</option>
														<option value='Norway'>Norway</option>
														<option value='Oman'>Oman</option>
														<option value='Pakistan'>Pakistan</option>
														<option value='Palau'>Palau</option>
														<option value='Palestinian Territory, Occupied'>Palestinian Territory, Occupied</option>
														<option value='Panama'>Panama</option>
														<option value='Papua New Guinea'>Papua New Guinea</option>
														<option value='Paraguay'>Paraguay</option>
														<option value='Peru'>Peru</option>
														<option value='Philippines'>Philippines</option>
														<option value='Pitcairn'>Pitcairn</option>
														<option value='Poland'>Poland</option>
														<option value='Portugal'>Portugal</option>
														<option value='Puerto Rico'>Puerto Rico</option>
														<option value='Qatar'>Qatar</option>
														<option value='Reunion'>Reunion</option>
														<option value='Romania'>Romania</option>
														<option value='Russian Federation'>Russian Federation</option>
														<option value='Rwanda'>Rwanda</option>
														<option value='Saint Helena'>Saint Helena</option>
														<option value='Saint Kitts and Nevis'>Saint Kitts and Nevis</option>
														<option value='Saint Lucia'>Saint Lucia</option>
														<option value='Saint Pierre and Miquelon'>Saint Pierre and Miquelon</option>
														<option value='Saint Vincent and The Grenadines'>Saint Vincent and The Grenadines</option>
														<option value='Samoa'>Samoa</option>
														<option value='San Marino'>San Marino</option>
														<option value='Sao Tome and Principe'>Sao Tome and Principe</option>
														<option value='Saudi Arabia'>Saudi Arabia</option>
														<option value='Senegal'>Senegal</option>
														<option value='Serbia'>Serbia</option>
														<option value='Seychelles'>Seychelles</option>
														<option value='Sierra Leone'>Sierra Leone</option>
														<option value='Singapore'>Singapore</option>
														<option value='Slovakia'>Slovakia</option>
														<option value='Slovenia'>Slovenia</option>
														<option value='Solomon Islands'>Solomon Islands</option>
														<option value='Somalia'>Somalia</option>
														<option value='South Africa'>South Africa</option>
														<option value='South Georgia and The South Sandwich Islands'>South Georgia and The South Sandwich Islands</option>
														<option value='Spain'>Spain</option>
														<option value='Sri Lanka'>Sri Lanka</option>
														<option value='Sudan'>Sudan</option>
														<option value='Suriname'>Suriname</option>
														<option value='Svalbard and Jan Mayen'>Svalbard and Jan Mayen</option>
														<option value='Swaziland'>Swaziland</option>
														<option value='Sweden'>Sweden</option>
														<option value='Switzerland'>Switzerland</option>
														<option value='Syrian Arab Republic'>Syrian Arab Republic</option>
														<option value='Taiwan, Province of China'>Taiwan, Province of China</option>
														<option value='Tajikistan'>Tajikistan</option>
														<option value='Tanzania, United Republic of'>Tanzania, United Republic of</option>
														<option value='Thailand'>Thailand</option>
														<option value='Timor-leste'>Timor-leste</option>
														<option value='Togo'>Togo</option>
														<option value='Tokelau'>Tokelau</option>
														<option value='Tonga'>Tonga</option>
														<option value='Trinidad and Tobago'>Trinidad and Tobago</option>
														<option value='Tunisia'>Tunisia</option>
														<option value='Turkey'>Turkey</option>
														<option value='Turkmenistan'>Turkmenistan</option>
														<option value='Turks and Caicos Islands'>Turks and Caicos Islands</option>
														<option value='Tuvalu'>Tuvalu</option>
														<option value='Uganda'>Uganda</option>
														<option value='Ukraine'>Ukraine</option>
														<option value='United Arab Emirates'>United Arab Emirates</option>
														<option value='United Kingdom'>United Kingdom</option>
														<option value='United States'>United States</option>
														<option value='United States Minor Outlying Islands'>United States Minor Outlying Islands</option>
														<option value='Uruguay'>Uruguay</option>
														<option value='Uzbekistan'>Uzbekistan</option>
														<option value='Vanuatu'>Vanuatu</option>
														<option value='Venezuela'>Venezuela</option>
														<option value='Viet Nam'>Viet Nam</option>
														<option value='Virgin Islands, British'>Virgin Islands, British</option>
														<option value='Virgin Islands, U.S.'>Virgin Islands, U.S.</option>
														<option value='Wallis and Futuna'>Wallis and Futuna</option>
														<option value='Western Sahara'>Western Sahara</option>
														<option value='Yemen'>Yemen</option>
														<option value='Zambia'>Zambia</option>
														<option value='Zimbabwe'>Zimbabwe</option>
													</select>

													<input type='submit' name='changecountry' value='Change Country' class='boxButton'>
												</td>
											</tr>
										</table>
									</form>
								</div>

								<div class='boxArea'>
									<form action='' method='POST'>
										<table>
											<tr>
												<td width='200'>Timezone:</td>
												<td>
													<select id='timezone' name='timezone' class='boxFormInput'>
												    	<option value='-12.0'>(GMT -12:00) Eniwetok, Kwajalein</option>
												    	<option value='-11.0'>(GMT -11:00) Midway Island, Samoa</option>
												    	<option value='-10.0'>(GMT -10:00) Hawaii</option>
												    	<option value='-9.0'>(GMT -9:00) Alaska</option>
												    	<option value='-8.0'>(GMT -8:00) Pacific Time (US & Canada)</option>
												    	<option value='-7.0'>(GMT -7:00) Mountain Time (US & Canada)</option>
												    	<option value='-6.0'>(GMT -6:00) Central Time (US & Canada), Mexico City</option>
												    	<option value='-5.0'>(GMT -5:00) Eastern Time (US & Canada), Bogota, Lima</option>
												    	<option value='-4.0'>(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>
												    	<option value='-3.5'>(GMT -3:30) Newfoundland</option>
												    	<option value='-3.0'>(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>
												    	<option value='-2.0'>(GMT -2:00) Mid-Atlantic</option>
												    	<option value='-1.0'>(GMT -1:00 hour) Azores, Cape Verde Islands</option>
												    	<option value='0.0'>(GMT) Western Europe Time, London, Lisbon, Casablanca</option>
												    	<option value='1.0'>(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris</option>
												    	<option value='2.0'>(GMT +2:00) Kaliningrad, South Africa</option>
												    	<option value='3.0'>(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>
												    	<option value='3.5'>(GMT +3:30) Tehran</option>
												    	<option value='4.0'>(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
												    	<option value='4.5'>(GMT +4:30) Kabul</option>
												    	<option value='5.0'>(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
												    	<option value='5.5'>(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>
												    	<option value='5.75'>(GMT +5:45) Kathmandu</option>
												    	<option value='6.0'>(GMT +6:00) Almaty, Dhaka, Colombo</option>
												    	<option value='7.0'>(GMT +7:00) Bangkok, Hanoi, Jakarta</option>
												    	<option value='8.0'>(GMT +8:00) Beijing, Perth, Singapore, Hong Kong</option>
												    	<option value='9.0'>(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
												    	<option value='9.5'>(GMT +9:30) Adelaide, Darwin</option>
												    	<option value='10.0'>(GMT +10:00) Eastern Australia, Guam, Vladivostok</option>
												    	<option value='11.0'>(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>
												    	<option value='12.0'>(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka</option>
													</select>

													<input type='submit' name='changetimezone' value='Change Timezone' class='boxButton'>
												</td>
											</tr>
										</table>
									</form>
								</div>";

								if($permissions['setowntitle'])
								{
									echo
									"<div class='boxArea'>
										<form action='' method='POST'>
											<table>
												<tr>
													<td width='200'>User Title:</td>
													<td><input type='text' name='usertitle' placeholder='User Title' value='" . $user['usertitle'] . "' maxlength='1000' class='boxFormInput'> <input type='submit' name='changeusertitle' value='Change User Title' class='boxButton'></td>
												</tr>
											</table>
										</form>
									</div>";
								}

							echo	
							"</div>
						</div>";

						break;
					}

					case "avatar":
					{
						if($permissions['uploadavatar'])
						{
							if($_POST['uploadavatar'])
							{
								list($imageWidth, $imageHeight, $imageType, $imageAttribute) = getimagesize($_FILES['avatar']['tmp_name']);

								if($imageWidth && $imageHeight)
								{
									if(filesize($_FILES['avatar']['tmp_name']) <= $permissions['maxavatarsize'])
									{
										$fileExtension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

										if($fileExtension == "bmp" || $fileExtension == "gif" || $fileExtension == "jpg" || $fileExtension == "jpeg" || $fileExtension == "png")
										{
											if($fileExtension != "gif" || $permissions['animatedavatar'])
											{
												$nextUploadIndex = file_get_contents("avatars/next") + 1;

												mkdir("avatars/$nextUploadIndex");

												file_put_contents("avatars/next", $nextUploadIndex);

												if(move_uploaded_file($_FILES['avatar']['tmp_name'], "avatars/$nextUploadIndex/" . $_FILES['avatar']['name'] . ""))
												{
													$mysql->query("UPDATE `accounts` SET `avatar` = 'avatars/$nextUploadIndex/" . escape($_FILES['avatar']['name']) . "' WHERE `id` = '" . $_SESSION['accountid'] . "'");
													redirect("");
												}
												else echo "There was an unknown problem while uploading the image. Please try again later. <br> <br>";
											}
											else echo "You are not allowed to have an animated avatar. <br> <br>";
										}
										else echo "You did not select a valid image. <br> <br>";
									}
									else echo "The image you have selected is too big. Make sure the image file size is below " . $permissions['maxavatarsize'] . " bytes. <br> <br>";
								}
								else echo "You did not select a valid image. <br> <br>";
							}
							else if($_POST['webavatar'])
							{
								list($imageWidth, $imageHeight, $imageType, $imageAttribute) = getimagesize($_POST['avatar']);

								if($imageWidth && $imageHeight)
								{
									if(filesize($_POST['avatar']) <= $permissions['maxavatarsize'])
									{
										$fileExtension = strtolower(pathinfo($_POST['avatar'], PATHINFO_EXTENSION));

										if($fileExtension == "bmp" || $fileExtension == "gif" || $fileExtension == "jpg" || $fileExtension == "jpeg" || $fileExtension == "png")
										{
											if($fileExtension != "gif" || $permissions['animatedavatar'])
											{
												$mysql->query("UPDATE `accounts` SET `avatar` = '" . escape($_POST['avatar']) . "' WHERE `id` = '" . $_SESSION['accountid'] . "'");
												redirect("");
											}
											else echo "You are not allowed to have an animated avatar. <br> <br>";
										}
										else echo "You did not select a valid image. <br> <br>";
									}
									else echo "The image you have selected is too big. Make sure the image file size is below " . $permissions['maxavatarsize'] . " bytes. <br> <br>";
								}
								else echo "You did not select a valid image. <br> <br>";
							}

							echo
							"<div class='settingsAvatar'>
								 <img src='" . $user['avatar'] . "' $userAvatarStyle>
							</div>

							<br><br>

							<h2 class='bold'>
								Upload from your computer
							</h2>

							<form action='' method='POST' enctype='multipart/form-data'>
								<input type='file' name='avatar'>
								<input type='submit' name='uploadavatar' value='Upload Avatar' class='boxButton'>
							</form>

							<br> <br>

							<h2 class='bold'>
								Get image from website
							</h2>

							<form action='' method='POST'>
								<input type='text' name='avatar' placeholder='URL (ex: http://lawlessboards.com/picture.png)' class='boxFormInput'>
								<input type='submit' name='webavatar' value='Select Avatar' class='boxButton'>
							</form>";
						}

						break;
					}

					case "signature":
					{
						if($permissions['allowsignature'])
						{
							if($_POST['changesignature'])
							{
								if((stripos($_POST['signature'], "[IMG]") !== true && stripos($_POST['signature'], "[/IMG]") !== true) || $permissions['signatureimage'])
								{
									if(stripos($_POST['signature'], ".gif[/IMG]") !== true || $permissions['signatureanimatedimage'])
									{
										if(strlen($_POST['signature']) <= $permissions['maxsignature'])
										{
											if(substr_count($_POST['signature'], "\n") <= $permissions['maxsignaturelines'])
											{
												$mysql->query("UPDATE `accounts` SET `signature` = '" . escape($_POST['signature']) . "' WHERE `id` = '" . $_SESSION['accountid'] . "'");
												redirect("");
											}
											else echo "The signature you have entered has too many lines. It may only have " . $permissions['maxsignaturelines'] . " lines. <br> <br>";
										}
										else echo "The signature you have entered is too long. It must be below " . $permissions['maxsignature'] . " characters. <br> <br>";
									}
									else echo "You may not have animated images in your signature. <br> <br>";
								}
								else echo "You may not have images in your signature. <br> <br>";
							}

							$bbParser->parse($user['signature']);

							echo
							"<form action='' method='POST'>
								<div class='box'>
									<div class='boxHeading'>
										Modify Signature
									</div>

									<div class='boxMain'>
										<div class='boxArea'>
											" . nl2br(unescape($bbParser->getAsHtml())) . "
										</div>

										<div class='boxArea'>";

											if($permissions['signaturebbcode'])
											{
												echo
												"<button type='button' data-tag='B' class='bbcode boxButton'>bold</button>
												<button type='button' data-tag='I' class='bbcode boxButton'>italic</button>
												<button type='button' data-tag='U' class='bbcode boxButton'>underline</button>
												<button type='button' data-tag='LEFT' class='bbcode boxButton'>left</button>
												<button type='button' data-tag='CENTER' class='bbcode boxButton'>center</button>
												<button type='button' data-tag='RIGHT' class='bbcode boxButton'>right</button>
												<button type='button' data-tag='LIST' class='bbcode boxButton'>bullet list</button>
												<button type='button' data-tag='NLIST' class='bbcode boxButton'>number list</button>
												<button type='button' data-tag='LI' class='bbcode boxButton'>list item</button>
												<button type='button' data-tag='EMAIL' class='bbcode boxButton'>email</button>";

												if($permissions['signatureimage'])
												{
													echo " <button type='button' data-tag='IMG' class='bbcode boxButton'>image</button> ";
												}

												echo
												"<button type='button' data-tag='QUOTE' class='bbcode boxButton'>quote</button>
												<button id='bbcode-link' type='button' data-tooltip='Example: [URL=http://example.com]Click here[/URL]' class='boxButton'>link</button>
												<button id='bbcode-font' type='button' data-tooltip='Example: [FONT=Arial]Hello world![/FONT]' class='boxButton'>font</button>
												<button id='bbcode-size' type='button' data-tooltip='Example: [SIZE=5]Hello world![/SIZE]' class='boxButton'>size</button>
												<button id='bbcode-color' type='button' data-tooltip='Example: [COLOR=RED]Hello[/COLOR] [COLOR=#00FF00]world![/COLOR]' class='boxButton'>color</button> ";

												if($permissions['mentionusers'])
												{
													echo "<button type='button' data-tag='MENTION' data-tooltip='Example: [MENTION]Jimmy[/MENTION]' class='bbcode boxButton'>mention</button>";
												}

												echo "<br><br>";
											}

											echo
											"<textarea id='message' name='signature' placeholder=' Signature' maxlength='" . $permissions['maxsignature'] . "' class='boxTextArea' autofocus>" . $user['signature'] . "</textarea>
										</div>
									</div>
								</div>

								<div align='right'>
									<input type='submit' name='changesignature' value='Change Signature' class='boxButton'>
								</div>
							</form>";
						}

						break;
					}

					case "hidden":
					{
						$hidden = ($user['hidden']) ? 0 : 1;
						$mysql->query("UPDATE `accounts` SET `hidden` = '$hidden' WHERE `id` = '" . $_SESSION['accountid'] . "'");
						redirect("?view=" . $_GET['from'] . "");
						break;
					}

					default:
					{
						redirect("?view=profile");
						break;
					}
				}

			?>
		</td>
	</tr>
</table>

<script>
	$(document).ready(function()
	{
		$('#country option[value="<?php echo $user['country']; ?>"]').attr("selected", "selected");
		$('#timezone option[value="<?php echo $user['timezone']; ?>"]').attr("selected", "selected");

		function insertString(string, insert, position)
		{
			return string.substr(0, position) + insert + string.substr(position);
		}

		function wrapText(before, after)
		{
			var textSelection = $('#message').getSelection(), selectionStart = textSelection.start, selectionEnd = textSelection.end;
			$('#message').val(insertString($('#message').val(), after, selectionEnd)).val(insertString($('#message').val(), before, selectionStart));
			$('#message').setSelection(selectionStart + before.length);
		}

		$('.bbcode').click(function()
		{
			wrapText("[" + $(this).data("tag") + "]", "[/" + $(this).data("tag") + "]");
		});

		$('#bbcode-link').click(function()
		{
			wrapText("[URL=http://example.com]", "[/URL]");
		});

		$('#bbcode-font').click(function()
		{
			wrapText("[FONT=Arial]", "[/FONT]");
		});

		$('#bbcode-size').click(function()
		{
			wrapText("[SIZE=5]", "[/SIZE]");
		});

		$('#bbcode-color').click(function()
		{
			wrapText("[COLOR=RED]", "[/COLOR]");
		});
	});
</script>

<?php

	require_once("includes/footer.php");

?>