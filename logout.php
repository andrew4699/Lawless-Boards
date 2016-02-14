<?php

	require_once("configuration/main.php");

	$_SESSION['accountid'] = 0;

	cookie("lb_accountid", 0);
	cookie("lb_username", 0);
	cookie("lb_password", 0);

	redirect("index");

?>

<?php

	require_once("includes/footer.php");

?>