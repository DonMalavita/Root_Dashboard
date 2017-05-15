<?php
require_once PROJECT_DIR . '/resources/config.php';

if(Session::exists("msg_search")) {
   echo "<p>" . Session::flash("msg_search") . "</p>";
}

if(Input::exists()) {


}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1"
 http-equiv="content-type">
  <title>Search</title>
</head>
<body>
  <div id="search_box">
<form target="_self" enctype="application/x-www-form-urlencoded" method="get"
 action="search.php" name="crawler-search">Search Query<br>
  <input name="search-query"><br>
  <button value="Submit" name="submit"></button></form>
</div>
</body>
</html>
