<?php
require_once '../header.php';

$user = new User();
$validate = new Validate();

if(!$user->isLoggedIn()) {
    Redirect::to("home");
}

if(Input::exists()) {
    if(Token::check(Input::get("token"))) {
        $validate->addRule($_POST, array(
            'huidig_wachtwoord' => array(
                'required' => true,
                'min' => 6
            ),
             'nieuw_wachtwoord' => array(
                'required' => true,
                'min' => 6
            ),
             'nieuw_wachtwoord_herhalen' => array(
                'required' => true,
                'min' => 6,
                'matches' => 'nieuw_wachtwoord'
            ),
        ));
        
        if($validate->passed()) {
            if(Hash::make(Input::get("huidig_wachtwoord"),  $user->data()->salt) !== $user->data()->password) {
                echo "Het huidige wachtwoord is incorrect!";
            } else {
                $salt = Hash::salt(32);
                $user->update(array(
                    'password' => Hash::make(Input::get("nieuw_wachtwoord"), $salt),
                    'salt' => $salt
                ));
                
                if($user->data()) {
                Session::flash("home", "U heeft zojuist uw wachtwoord veranderd!");
                Redirect::to("home");
                }
                
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
        <label for="huidig_wachtwoord">Huidig wachtwoord: </label>
        <input type="password" name="huidig_wachtwoord" value="">
    </div>
    
    <div class="field">
        <label for="nieuw_wachtwoord">Nieuw wachtwoord: </label>
        <input type="password" name="nieuw_wachtwoord" value="">
    </div>
        
    <div class="field">
        <label for="nieuw_wachtwoord_herhalen">Herhaal wachtwoord: </label>
        <input type="password" name="nieuw_wachtwoord_herhalen" value="">
    </div>
    
    <input type="submit" value="submit">
    <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
 
</form>