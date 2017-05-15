<?php


if(Input::exists()) {
    if(Token::check(Input::get('token'))) {
    $validate = new Validate();
    $validation = $validate->addRule($_POST, array(
       'name' => array(
           'required' => true,
           'min' => 2,
           'max' => 50,
       ),
        'username' => array(
            'required' => true,
            'min' => 2,
            'max' => 20,
            'unique' => 'users'
        ),
        'password' => array(
            'required' => true,
            'min' => 6
        ),
        'password_again' => array(
            'required' => true,
            'min' => 6,
            'matches' => 'password'
        ),
        
    ));
    
    if ($validate->passed()) {
        $user = DB::getInstance();
        $salt   = Hash::salt(32);
        try {
            
           if ($user->insert("users",array(
                'username' => Input::get('username'),
                'password' => Hash::make(Input::get('password'), $salt),
                'salt' => $salt,
                'name' => Input::get('name'),
                'joined' => date('Y/m/d H:i:s'),
                'group' => 1
            ))) {
               Session::flash('home','U bent nu geregistreerd en u kan inloggen.');
               Redirect::to('index.php');
            } else {
               Session::flash('home','Het registreren is fout gegaan, graag opnieuw het formulier invullen.');
               Redirect::to('index.php');
            }
            
        } catch (Exception $e) {
            die($e->getMessage());
        }
    } else {
        foreach($validate->errors() as $err) {
         echo $err.'<br />';
        }
    }
    }
}


