<?php
require 'C:\wamp\apache2\htdocs\Root_Dashboard\resources\init.php';

switch ($_GET['page']) {
	case "home":
		$smarty->tpl->display('footer.tpl');
}