<?php
require_once PROJECT_DIR . '/resources/config.php';

if(Session::exists("msg_login")) {
   echo "<p>" . Session::flash("msg_login") . "</p>";
}

if(Input::exists()) {
    if(Token::check(Input::get('token'))) {

        $validate = new Validate();
        $validation = $validate->addRule($_POST, array(
            'gebruikersnaam' => array('required' => true),
            'wachtwoord' => array('required' => true)
        ));

        if($validate->passed()) {
            $user = new User();
            $remember = (Input::get('onthouden') === 'ja') ? true : false;

            if($user->login(Input::get('gebruikersnaam'), Input::get('wachtwoord'), $remember)) {
                Session::flash("msg_home", "Succesvol ingelogd!");
                URL::redirect("home");
            } else {
                Session::flash("msg_login", "Het inloggen ging fout, het kan een typo zijn geweest controleer uw gegevens.");
                URL::redirect("members/login");
            }

        } else {
            foreach ($validate->errors() as $error) {
                echo $error, '<br />';
            }
        }

    }
}

?>

 <form action="" method="post">
     <div class="field">
         <label for="gebruikersnaam">Gebruikersnaam</label>
         <input type="text" name="gebruikersnaam" id="gebruikersnaam" autocomplete="on">
     </div>

     <div class="field">
         <label for="wachtwoord">Wachtwoord</label>
         <input type="password" name="wachtwoord" id="wachtwoord" autocomplete="off">
     </div>
     <div class="field">
         <label for="onthouden">
             <input type="checkbox" name="onthouden" id="onthouden" value="ja"> Onthoud mij! (7 dagen)
         </label>
     </div>
     <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
     <input type="submit" value="Inloggen">
 </form>
