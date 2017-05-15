<?php
require_once '../header.php';

$user = new User();
$sec = new Security();

if(!$user->isLoggedIn()) {
    Redirect::header(404);
}

if(Input::exists()) {
    if(Token::check(Input::get("token"))) {
        $validate = new Validate();
        $validate->addRule($_POST, array(
            'naam' => array(
                'required' => true,
                'min' => 2,
                'max' => 50
            )
        ));
        
        if($validate->passed()) {
            try {
                $user->update(array(
                    'name' => Input::get('naam')
                ));
                
                Session::flash("home", "Details aangepast.");
                Redirect::to("home");
            } catch (Exception $e) {
                die($e->getMessage());
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
        <label for="">Naam: </label>
        <input type="text" name="naam" value="<?php echo Validate::escape($user->data()->name); ?>">
        
        <input type="submit" value="submit">
        <input tpe="hidden" name="token" value="<?php echo Token::generate(); ?>">
    </div>
</form>