<?php
/*
 * User.class.php
 * ---------------------------------------
 * Deze class bevat alle methods betreft
 * gebruikers.
 * 
 */
class User {
    
    private  $_db,
             $_data,
             $_sessionName,
             $_cookieName,
             $_isLoggedIn = null;


    /**
     * User constructor.
     * @param null $user
     */
    public function __construct($user = null) 
    {
        $this->_db = DB::getInstance();
        $this->_sessionName = Config::get('session/session_name');
        $this->_cookieName = Config::get('cookie-jar/remember/cookie_name');
        
        if(Session::exists($this->_sessionName) && !$user){
            $user = Session::get($this->_sessionName);
    
                    if($this->find($user)){
                         $this->_isLoggedIn = true;
                     } else {
                         $this->logout();
                     }
        } else {
            $this->find($user);
        }
    }

    /**
     * Functie: update
     * @param array $fields
     * @param null $id
     * @throws Exception
     */
    public function update ($fields = array(), $id = null) 
    {
            if(!$id && $this->isLoggedIn()) {
                $id = $this->data()->id;
            }

            if(!$this->_db->update('users', $id, $fields)) {
                throw new Exception('Er was een probleem bij het updaten van uw gegevens.');
            }
        }

    /**
     * Functie: create
     * @param array $fields
     * @throws Exception
     */
    public function create($fields = array()) 
    {
        if(!$this->_db->insert('users', $fields)) {
            throw new Exception('Ooops... daar ging iets fout met de database.');
        }
    }

    /**
     * Functie: find
     * @param null $user
     * @return bool
     */
    public function find($user = null) 
    {
        if($user) {
            $field = (is_numeric($user)) ? 'id' : 'username';
            $data = $this->_db->get('users', array($field, '=', $user));         
            
            if(($data) && ($data->count())) {
                $this->_data = $data->first();
                return true;
            } else {
                return false;
            }            
        }
        return false;
    }

    /**
     * Functie: login
     * @param null $user
     * @param null $pass
     * @param bool $remember
     * @return bool
     */
    public function login($user = null, $pass = null, $remember = false) 
    {
        
        if (!$user && !$pass && $this->exists()) {
            Session::put($this->_sessionName, $this->data()->id);
        } else {        
                if(isset($user) && ($this->find($user))) {
                    if($this->data()->password === Hash::make($pass, $this->data()->salt)) {
                        Session::put($this->_sessionName, $this->data()->id);
                        if($remember) {
                            $hash = Hash::unique();
                            $hashCheck = $this->_db->get('user_sessions', array('id', '=', $this->data()->id));


                            if(!$hashCheck->count()) {
                                $this->_db->insert('user_sessions', array(
                                    'id' => $this->data()->id,
                                    'hash' => $hash
                                ));
                            } else {
                                $hash = $hashCheck->first()->hash;
                            }
                            
                            Cookie::put($this->_cookieName, $hash, Config::get("remember/cookie_expire"));
                            Session::flash("home","Succesvol ingelogd!");
                            $this->_isLoggedIn = true;
                            
                        }                                
                        return true;
                    }
                }
        }
    }

    /**
     * Functie: logout
     * @param null $redirect
     * @return bool|void
     */
    public function logout($redirect = NULL) 
    {
        DB::getInstance()->delete('user_sessions', array('id', '=', self::data()->id));
        Session::delete($this->_sessionName);
        Cookie::delete($this->_cookieName);
        Session::flash("home","Succesvol uitgelogd.");
        return (isset($redirect)) ? URL::redirect($redirect) : Session::flash("home","Uitloggen mislukt.") && URL::redirect("home");        
    }

    /**
     * Functie: Allow
     * @param string $key
     * @return bool
     */
    public function Allow($key = '') 
    {
        $this->_db->get('groups', array('id', '=', $this->data()->group)); // Verwar je niet met de 'id' column
        if($this->_db->count()) {                                         // die is als alternatief voor groepsnaam
            $perm = json_decode($this->_db->first()->permissions, true);            
            if($perm[$key] === 1) {
                return true;
            }            
            return false;
        }
        
        return false;
    }

	/**
     * Functie: exists
     * @return bool
     */
    public function exists() 
    {
        return (!empty($this->_data)) ? true : false;
    }

	/**
     * Functie: data
     * @return mixed
     */
    public function data() 
    {
        return self::$_data;
    }

	/**
     * Functie: isLoggedIn
     * @return bool|null
     */
    public function isLoggedIn() 
    {
        return self::$_isLoggedIn;
    }

}