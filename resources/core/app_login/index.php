<?php

if(Session::exists("home")) {
   echo "<p>" . Session::flash("home") . "</p>";
}

$user = new User();
$cookie = new Cookie();

if($user->isLoggedIn()) {
    $userprofile = "user.php?u_id=" . $user->data()->id;

?>

<p>Hallo, <a href="<?php echo $userprofile; ?>"><?php echo $user->data()->username; ?></a></p>
<p><a href="logout.php">Klik hier</a> om uit te loggen.</p>

<?php
} else {
?>
<p>Hallo, <a href="login.php">Login</a> of <a href="register.php">Registreer</a> om verder te gaan.</p>
<?php
}
