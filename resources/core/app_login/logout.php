<?php
require_once '../header.php';

if(Session::exists(Config::get("session/session_name"))) {

$usr = new User();

if(empty($usr->logout())) {
    Session::flash("home", "Er is een fout opgetreden, ben je wel ingelogd?");
    Redirect::to("home");
} else {
    Session::flash("home", "Succesvol uitgelogd!");
    Redirect::to("home");
}

} else {
    Redirect::to("home");
}