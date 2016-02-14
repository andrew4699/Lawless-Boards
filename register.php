<?php

	require_once("configuration/main.php");

	if($_SESSION['accountid'])
	{
		redirect("index");
	}

	setPageInfo("Registration", "");

	if($_GET['key'])
	{
		$mQuery = $mysql->query("SELECT `id`, `username`, `password` FROM `accounts` WHERE `key` = '" . escape($_GET['key']) . "' AND `verified` IS NULL");

		if($mQuery->num_rows)
		{
			$mData = $mQuery->fetch_assoc();
			$_SESSION['accountid'] = $mData['id'];

			cookie("lb_accountid", $mData['id']);
			cookie("lb_username", $mData['username']);
			cookie("lb_password", $mData['password']);

			$mysql->query("UPDATE `accounts` SET `verified` = '1' WHERE `id` = '" . $mData['id'] . "'");

			echo "Your account has been verified.";
			redirect("index", 2);
		}
		else
		{
			die("You have followed an invalid link.");
		}
	}
	else if($_POST['register'])
	{
		if(strlen($_POST['username']) >= 2)
		{
			if(strlen($_POST['password']) >= 4)
			{
				if($_POST['confirmpassword'])
				{
					if($_POST['password'] == $_POST['confirmpassword'])
					{
						if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && strpos($_POST['email'], "."))
						{
							if($_POST['confirmemail'])
							{
								if($_POST['email'] == $_POST['confirmemail'])
								{
									if(strtolower($_POST['captcha']) == $_SESSION['captcha'])
									{
										if($_POST['agree'] == "on")
										{
											$mQuery = $mysql->query("SELECT `id` FROM `accounts` WHERE `username` = '" . escape($_POST['username']) . "'");

											if(!$mQuery->num_rows)
											{
												$mQuery = $mysql->query("SELECT `id` FROM `accounts` WHERE `displayname` = '" . escape($_POST['displayname']) . "'");

												if(!$mQuery->num_rows)
												{
													$mQuery = $mysql->query("SELECT `id` FROM `accounts` WHERE `email` = '" . escape($_POST['email']) . "'");

													if(!$mQuery->num_rows)
													{
														$validationKey = md5(rand());

														email($_POST['email'], "Lawless Boards: Account Validation", "Dear " . $_POST['displayname'] . ",\r\n\r\nThank you for registering at Lawless Boards.\r\n\r\nTo fully activate your account, please click on the link below:\r\n" . getCurrentPage() . "?key=$validationKey");
														
														$mysql->query("INSERT INTO `accounts` (`username`, `password`, `displayname`, `email`, `birthday`, `country`, `timezone`, `key`, `ip`) VALUES ('" . escape($_POST['username']) . "', '" . password($_POST['password']) . "', '" . escape($_POST['displayname']) . "', '" . escape($_POST['email']) . "', '" . escape($_POST['birthday']) . "', '" . escape($_POST['country']) . "', '" . escape($_POST['timezone']) . "', '$validationKey', '" . $_SERVER['REMOTE_ADDR'] . "')");
														addUserGroup($mysql->insert_id, REGISTERED_GROUP, true);

														die
														("
															<div class='box'>
																<div class='boxHeading'>
																	You have successfully registered an account. Please check your email inbox to activate it.
																</div>
															</div>
														");
													}
													else
													{
														echo
														"<div class='box'>
															<div class='boxHeading'>
																An account with this email is already registered.
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
															An account with this display name is already registered.
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
														An account with this user name is already registered.
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
													You did not agree to the rules.
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
												You have entered the captcha incorrectly.
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
											Your email address does not match your confirmed email address.
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
										You have not entered a confirmed email address.
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
									You have entered an invalid email address.
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
								Your password does not match your confirmed password.
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
							You have not entered a confirmed password.
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
						Your password must be at least 4 characters long.
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
					Your username must be at least 2 characters long.
				</div>
			</div>

			<br>";
		}
	}

?>

<form action='register' method='POST'>
	<div class='box'>
		<div class='boxHeading'>
			Register at Lawless Boards
		</div>

		<div class='boxSubHeading'>
			Basic Information
		</div>

		<div class='boxMain'>
			<div class='boxArea'>
				<table>
					<tr>
						<td width='300'>User Name:</td>
						<td><input type='text' name='username' placeholder='User Name' value='<?php echo $_POST['username']; ?>' maxlength='50' class='boxFormInput' autofocus required></td>
					</tr>
				</table>
			</div>

			<div class='boxArea'>
				<table>
					<tr>
						<td width='300'>Password:</td>
						<td><input type='password' name='password' placeholder='Password' value='<?php echo $_POST['password']; ?>' class='boxFormInput' required></td>
					</tr>

					<tr>
						<td width='300'>Confirm Password:</td>
						<td><input type='password' name='confirmpassword' placeholder='Confirm Password' value='<?php echo $_POST['confirmpassword']; ?>' class='boxFormInput'required></td>
					</tr>
				</table>
			</div>

			<div class='boxArea'>
				<table>
					<tr>
						<td width='300'>Display Name:</td>
						<td><input type='text' name='displayname' placeholder='Display Name' value='<?php echo $_POST['displayname']; ?>' maxlength='50' class='boxFormInput' required></td>
					</tr>
				</table>
			</div>

			<div class='boxArea'>
				<table>
					<tr>
						<td width='300'>Email Address:</td>
						<td><input type='text' name='email' placeholder='Email Address' value='<?php echo $_POST['email']; ?>' class='boxFormInput' required></td>
					</tr>

					<tr>
						<td width='300'>Confirm Email Address:</td>
						<td><input type='text' name='confirmemail' placeholder='Confirm Email Address' value='<?php echo $_POST['confirmemail']; ?>' class='boxFormInput' required></td>
					</tr>
				</table>
			</div>
		</div>

		<div class='boxSubHeading'>
			Additional Information
		</div>

		<div class='boxMain'>
			<div class='boxArea'>
				<table>
					<tr>
						<td width='300'>Birthday:</td>
						<td><input type='date' name='birthday' placeholder='Birthday' value='<?php echo $_POST['birthday']; ?>' class='boxFormInput'></td>
					</tr>
				</table>
			</div>

			<div class='boxArea'>
				<table>
					<tr>
						<td width='300'>Country:</td>

						<td>
							<select name='country' class='boxFormInput'>
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
								<option value='United States' selected>United States</option>
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
						</td>
					</tr>
				</table>
			</div>

			<div class='boxArea'>
				<table>
					<tr>
						<td width='300'>Timezone:</td>

						<td>
							<select name='timezone' class='boxFormInput'>
						    	<option value='-12.0'>(GMT -12:00) Eniwetok, Kwajalein</option>
						    	<option value='-11.0'>(GMT -11:00) Midway Island, Samoa</option>
						    	<option value='-10.0'>(GMT -10:00) Hawaii</option>
						    	<option value='-9.0'>(GMT -9:00) Alaska</option>
						    	<option value='-8.0' selected>(GMT -8:00) Pacific Time (US & Canada)</option>
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
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div class='boxSubHeading'>
			Human Verification
		</div>

		<div class='boxMain'>
			<div class='boxArea'>
				<div id='captcha'>
					<div id='captchaFirst' class='hidden'>
						Slide the arrow to the right to continue

						<br><br>

						<div class='captchaBox'>
							<div id='captchaArrow' class='captchaArrow'>
					
							</div>
						</div>
					</div>

					<noscript>
						<img src='captcha'> <br> <input type='text' name='captcha' placeholder='Captcha' class='boxFormInput' required>
					</noscript>
				</div>
			</div>
		</div>
	</div>

	<br>

	<div class='box'>
		<div class='boxHeading'>
			Rules
		</div>

		<div class='boxMain'>
			In order to complete your registration, you must read and agree to the following rules:

			<br><br>

			<div class='boxSmallBox'>
				<div class='bold'>
					Rules
				</div>

				<br>

				The rules go here.
			</div>

			<br>

			<input type='checkbox' name='agree'> I have read and agree to the rules.
		</div>
	</div>

	<div align='right'>
		<input type='submit' name='register' value='Complete Registration' class='boxButton'>
	</div>
</form>

<script>
	$(document).ready(function()
	{
		$('#captchaFirst').show();
		
		$('#captchaArrow').draggable(
		{
			containment: "parent",
			stop: function(event, ui)
			{
				if(ui.position.left > 550)
				{
					$(this).animate({left: 830}, 350, function()
					{
						$('#captchaFirst').fadeOut(500, function()
						{
							$(this).remove();
							$('#captcha').append("<img src='captcha'> <br> <input type='text' name='captcha' placeholder='Captcha' class='boxFormInput' required>");
						});
					});
				}
				else
				{
					$(this).animate({left: 0}, 350);
				}
			}
		});
	});
</script>

<?php

	require_once("includes/footer.php");

?>