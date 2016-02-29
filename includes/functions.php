<?php
	function isLoggedIn ()
	{
		return isset($_SESSION['userId']) && is_numeric($_SESSION['userId']);
	}
