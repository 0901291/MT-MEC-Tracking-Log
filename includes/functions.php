<?php
	function isLoggedIn ()
	{
		return isset($_SESSION["isLoggedIn"]) && $_SESSION["isLoggedIn"] === true;
	};

	function checkAdmin ()
	{
		return isLoggedIn() && isset($_SESSION["isAdmin"]) && $_SESSION["isAdmin"] == true;
	};
