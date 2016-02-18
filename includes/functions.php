<?php
	function isLoggedIn ()
	{
		return isset($_SESSION["userId"]);
	};