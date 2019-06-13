<?php

namespace App\Models;

use Utility\Auth;
use App\Models\User;
use PDO;

class Role extends \Core\Model
{

    public static function addRole()
    {
        ;
    } 
    
    public static function getUsersRoles($user_id)
    {
//        $user = Auth::getUser();
        $db = static::getDB();
        $sql = "SELECT 
                    roles.name 
                FROM 
                    `role_user` 
                INNER JOIN roles
                    ON role_user.role_id = roles.id
                WHERE user_id = :user_id;";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } 

}