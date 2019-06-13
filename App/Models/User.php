<?php

namespace App\Models;

use Utility\Mail;
use Utility\Token;
use Core\View;
use PDO;

class User extends \Core\Model
{
    public $errors = [];
    public $roles;

    // We are creating a user dynamically for pdo fetch mode class, in findbyemail method
    public function __construct($data = [])
    {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }

//        $this->roles = $this->getRoles();
//        echo "<pre>";
//        print_r($this->roles);
//        echo "</pre>";
        
    }

    public function store()
    {
        $this->validate();

        if(empty($this->errors) ) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token->getHash();
            $this->activation_token = $token->getValue();

            $sql = "INSERT INTO 
                users (name, email, password, activation_hash) 
                VALUES (:name, :email, :password, :activation_hash)";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

            return $stmt->execute();
        }
        return false;
    }

    public function validate()
    {
        // Name
        if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }

        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }

        if(static::emailExists($this->email, $this->id ?? null) ) {
            $this->errors[] = 'email already taken';
        }

        // Password
        if(isset($this->password) && isset($this->passwordConfirmation) ) {
            if ($this->password != $this->passwordConfirmation) {
                $this->errors[] = 'Password must match confirmation';
            }

            if (strlen($this->password) < 6) {
                $this->errors[] = 'Please enter at least 6 characters for the password';
            }

            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one letter';
            }

            if (preg_match('/.*\d+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one number';
            }
        }
    }
    
    public static function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class() );

        $stmt->execute();

        return $stmt->fetch();
    } 

    public static function emailExists($email, $ignore_id = null)
    {
        $user = static::findByEmail($email);

        if($user)
            if($user->id != $ignore_id)
                return true;

        return false;
    }

    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);

        if ($user && $user->activated) {
            if(password_verify($password, $user->password) ) {
                return $user;
            }
        }

        return false;
    }

    public static function findById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id";

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class() );

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     *
     */
    public function rememberLogin()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();

        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30; // 30 days from now

        $sql = "INSERT INTO remembered_logins(token_hash, user_id, expires_at)
                VALUES (:token_hash, :user_id, :expires_at)";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }

    public static function sendPasswordReset($email)
    {
        $user = static::findByEmail($email);

        if($user) {
            if($user->startPasswordReset() ) {
                $user->sendPasswordResetEmail();
            }
        }
    }

    public function startPasswordReset()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->password_reset_token = $token->getValue();
        $expiry_timestamp = time() + 60 * 60 * 2; // 2 hours

        $sql = "UPDATE users
                SET 
                  password_reset_hash = :token_hash,
                  password_reset_expiry = :expires_at
                WHERE id = :id";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();

    }

    protected function sendPasswordResetEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

//        $text = "Please click on the following URL to reset your password: $url" ;
//        $html = "Please click <a href='{$url}'>here</a> to reset your password.";
        $text = View::getTemplate('Password/reset_email.text.twig', ['url' => $url]);
        $html = View::getTemplate('Password/reset_email.html.twig', ['url' => $url]);

        Mail::send($this->email, 'Password Reset', $html, $text);
    }
    
    public static function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash();

        $sql = "SELECT * FROM users
                WHERE password_reset_hash = :token_hash";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class() );

        $stmt->execute();

        $user = $stmt->fetch();

        if($user) {
            // Check password reset token hasn't expired
            if(strtotime($user->password_reset_expiry) > time() )
                return $user;
        }
    } 
    
    public function resetPassword($password, $passwordConfirmation)
    {
        $this->password = $password;
        $this->passwordConfirmation = $passwordConfirmation;
//        $this->validate();

        // Password
        if ($this->password != $this->passwordConfirmation) {
            $this->errors[] = 'Password must match confirmation';
        }

        if (strlen($this->password) < 6) {
            $this->errors[] = 'Please enter at least 6 characters for the password';
        }

        if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
            $this->errors[] = 'Password needs at least one letter';
        }

        if (preg_match('/.*\d+.*/i', $this->password) == 0) {
            $this->errors[] = 'Password needs at least one number';
        }

        if(empty($this->errors) ) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = "UPDATE users
                    SET 
                      password = :password_hash,
                      password_reset_hash = NULL,
                      password_reset_expiry = NULL
                    WHERE id = :id";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();

        }

        return false;
    }

    public function sendActivationEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/register/activate/' . $this->activation_token;

        $text = View::getTemplate('Auth/activation_email.text.twig', ['url' => $url]);
        $html = View::getTemplate('Auth/activation_email.html.twig', ['url' => $url]);

        Mail::send($this->email, 'Account activation', $html, $text);
    }
    
    public static function activate($value)
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();

        $sql = "UPDATE users
                SET 
                  activated = 1,
                  activation_hash = Null
                WHERE activation_hash = :hashed_token";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);

        $stmt->execute();
    }

    public function updateProfile($data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
//        $this->passwordConfirmation = $data['passwordConfirmation'];

        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }

        if(static::emailExists($this->email, $this->id ?? null) ) {
            $this->errors[] = 'email already taken';
        }

        if($data['password'] != '')
            $this->password = $data['password'];

        if($data['passwordConfirmation'] != '')
            $this->passwordConfirmation = $data['passwordConfirmation'];

        $this->validate();

        if(empty($this->errors) ) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = "UPDATE users 
                    SET name = :name, 
                        email = :email";

            // Add password if it is set
            if(isset($this->password))
                $sql.= ', password = :password_hash';

            $sql.= "\nWHERE id = :id";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            if(isset($this->password) ) {
                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            }

            return $stmt->execute();
        }

        return false;
    }

//    public function hasAnyRole($roles)
//    {
//        if(is_array($roles) )
//            foreach ($roles as $role)
//                if ($this->hasRole($role))
//                    return true;
//        else
//            if ($this->hasRole($roles))
//                return true;
//
//        return false;
//    }
//
//    public function hasRole($role)
//    {
//        if(in_array($role, $this->roles) )
//            return true;
//
//        return false;
//    }
//
//    public function getRoles()
//    {
//        return Role::getUsersRoles($this->id);
//    }
}