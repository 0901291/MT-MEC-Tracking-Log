<?php
	function isLoggedIn ()
	{
		if (DEBUG) return true;
		return isset($_SESSION["userId"]) && is_numeric($_SESSION["userId"]);
	}
