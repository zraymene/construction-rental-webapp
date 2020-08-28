<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */

require_once('config.php');

class Client 
{
    var $id;
    var $first_name;
    var $last_name;
    var $email;
    var $phone;
    var $num_rent;
    var $list_rents;
}

class Material 
{
    var $id;
    var $name;
    var $default_price;
    var $num_rents;
    var $is_free;
    var $list_clients;
    var $image_path;
}

class Rent 
{
    var $id;
    var $client_id;
    var $material_id;
    var $price;
    var $creation_date;     // YYYY-MM-DD HH:MM:SS
    var $deadline_date;    // YYYY-MM-DD
    var $author_id;
}

class Admin
{
    var $id;
    var $username;
    var $password;
    var $is_ceo;        // If true , can add/remove/edit other admins accounts
}

abstract class AbstractManger
{
    protected $add_query ;
    protected $delete_id_query;
    protected $select_id_query;

    protected $db_connection;

    public function __construct($dbconnection)
    {
        $this->db_connection = $dbconnection;
    }

    abstract protected function create_object($res);
    abstract protected function bind_param($ps, $obj);

    public function refresh_db_connection($dbconnection)    // Reconnect if connection is closed
    {
        $this->db_connection = $dbconnection;
    }

    // Returns wanted object , NULL on failure
    public function select_id($id)
    {
        $ps = $this->db_connection->prepare($this->select_id_query);

        if(!$ps)
        {
            echo $this->db_connection->error;
            return FALSE;
        }

        $ps->bind_param("i",$id);

        if(!$ps->execute())
        {
            $ps->close();
            return NULL;
        }

        $res = $ps->get_result()->fetch_assoc();

        $ps->close();
            return NULL;

        $obj = $this->create_object($res);

        $ps->close();

        return $obj;
    }

    // Returns 1 on success ; NULL on failure
    public function add($obj)
    {
        $ps = $this->db_connection->prepare($this->add_query);
        
        if(!$ps)
        {
            echo $this->db_connection->error;
            return FALSE;
        }


        $this->bind_param($ps, $obj);

        $return = $ps->execute();

        echo $this->db_connection->error;
        
        $ps->close();

        return $return;
    }

    // Returns number of effected rows , 0 on failure
    public function delete($id)
    {
        $ps = $this->db_connection->prepare($this->delete_id_query);

        $ps->bind_param("i",$id);

        $return = $ps->execute();
        $ps->close();

        return $return;
    }

}

class MaterialsManger extends AbstractManger
{

    public function __construct($dbconnection)
    {
        parent::__construct($dbconnection);

        $this->add_query       = "INSERT INTO ". DATABASE_NAME .".`materials` ( `name`, `default_price`, `is_free`, `list_clients`, `image_path`) VALUES ( ?, ?, ?, ?, ?);";
        $this->delete_id_query = "DELETE FROM ". DATABASE_NAME .".`materials` WHERE `id` = ?";
        $this->select_id_query = "SELECT * FROM ". DATABASE_NAME .".`materials` WHERE `id` = ?";
    }

    protected function create_object($res)
    {
        $obj = new Material();

        $obj->id            = $res['id'];
        $obj->name          = $res['name'];
        $obj->default_price = $res['default_price'];
        $obj->is_free       = $res['is_free'];
        $obj->list_clients  = json_decode($res['list_clients']);
        $obj->num_rents     = count($obj->list_clients);

        return $obj;
    }

    protected function bind_param($ps, $obj)
    {
        $json_str = json_encode($obj->list_clients);  // To prevent pass by refrence warning in bin_parm 

        $ps->bind_param("sdiss",
                    $obj->name,
                    $obj->default_price,
                    $obj->is_free,
                    $json_str,
                    $obj->image_path );

    }
}

class ClientsManger extends AbstractManger
{

    public function __construct($dbconnection)
    {
        parent::__construct($dbconnection);

        $this->add_query       = "INSERT INTO ". DATABASE_NAME .".`clients` ( `first_name`, `last_name` , `email`, `phone`, `list_rents`) VALUES ( ?, ?, ?, ?, ?);";
        $this->delete_id_query = "DELETE FROM ". DATABASE_NAME .".`clients` WHERE `id` = ?";
        $this->select_id_query = "SELECT * FROM ". DATABASE_NAME .".`clients` WHERE `id` = ?";
    }

    protected function create_object($res)
    {
        $obj = new Client();

        $obj->id         = $res['id'];
        $obj->first_name = $res['first_name'];
        $obj->last_name  = $res['last_name'];
        $obj->email      = $res['email'];
        $obj->phone      = $res['phone'];
        $obj->list_rents = json_decode($res['list_rents']);
        $obj->num_rents  = count($obj->list_rents);
        return $obj;
    }

    protected function bind_param($ps, $obj)
    {
        $json_str = json_encode($obj->list_rents);  // To prevent pass by refrence warning in bin_parm 

        $ps->bind_param('sssss',
                    $obj->first_name,
                    $obj->last_name,
                    $obj->email,
                    $obj->phone,
                    $json_str
                );

    }

    public function select_range($start , $end)
    {
        
    }
}

class RentsManger extends AbstractManger
{
    public function __construct($dbconnection)
    {
        parent::__construct($dbconnection);

        $this->add_query       = "INSERT INTO ". DATABASE_NAME .".`rents` ( `client_id`, `material_id`, `price`, `creation_date`, `deadline_date`, `author_id`) VALUES ( ?, ?, ?, ?, ?, ?);";
        $this->delete_id_query = "DELETE FROM ". DATABASE_NAME .".`rents` WHERE `id` = ?";
        $this->select_id_query = "SELECT * FROM ". DATABASE_NAME .".`rents` WHERE `id` = ?";
    }

    protected function create_object($res)
    {
        $obj = new Rent();
 
        $obj->id            = $res['id'];
        $obj->client_id     = $res['client_id'];
        $obj->material_id   = $res['material_id'];
        $obj->price         = $res['price'];
        $obj->creation_date = $res['creation_date'];
        $obj->deadline_date = $res['deadline_date'];
        $obj->author_id     = $res['author_id'];

        return $obj;
    }

    protected function bind_param($ps, $obj)
    {
        $ps->bind_param("iidss",
                    $obj->client_id,
                    $obj->material_id,
                    $obj->price,
                    $obj->creation_date,
                    $obj->deadline_date,
                    $obj->author_id);

    }
}

class AdminsManger extends AbstractManger
{
    public function __construct($dbconnection)
    {
        parent::__construct($dbconnection);

        $this->add_query       = "INSERT INTO ". DATABASE_NAME .".`admins` ( `username`, `password`, `is_ceo`) VALUES ( ?, ?, ?);";
        $this->delete_id_query = "DELETE FROM ". DATABASE_NAME .".`admins` WHERE `id` = ?";
        $this->select_id_query = "SELECT * FROM ". DATABASE_NAME .".`admins` WHERE `id` = ?";
    }

    protected function create_object($res)
    {
        $obj = new Admin();
 
        $obj->id          = $res['id'];
        $obj->username    = $res['username'];
        $obj->password    = $res['password'];
        $obj->is_ceo      = $res['is_ceo'];

        return $obj;
    }

    protected function bind_param($ps, $obj)
    {
        $hashed_pass = password_hash($obj->password, PASSWORD_DEFAULT);

        $ps->bind_param("ssi",
                    $obj->username,
                    $hashed_pass,
                    $obj->is_ceo);

    }

    public function auth($username , $password)
    {
        $auth_query = "SELECT * FROM ". DATABASE_NAME .".`admins` WHERE `username` = ?";
        
        is_resource($this->db_connection);

        $ps = $this->db_connection->prepare($auth_query);

        if(!$ps)
        {
            echo $this->db_connection->error;
            return NULL;
        }

        $ps->bind_param("s", $username);

        if(!$ps->execute())   
        {
            $ps->close();
            return NULL;
        }

        $res = $ps->get_result()->fetch_assoc();

        if($res == NULL)
        {
            $ps->close();
            return NULL;
        }

        if(!password_verify($password , $res['password']))
        {
            $ps->close();
            return NULL;
        }

        $obj = $this->create_object($res);

        $ps->close();

        return $obj;
    }
}

function refresh_mangers($obj , $new_connection)
{
    if($obj & ADMINS_MANGER_FLAG)
    {
        if(!isset($_SESSION['ADMINS_MANGER']))
            $_SESSION['ADMINS_MANGER'] = new AdminsManger($new_connection);
        else 
           $_SESSION['ADMINS_MANGER']->refresh_db_connection($new_connection);
    }

    if($obj & RENTS_MANGER_FLAG)
    {
        if(!isset($_SESSION['RENTS_MANGER']))
            $_SESSION['RENTS_MANGER'] = new RentsManger($new_connection);
        else 
           $_SESSION['RENTS_MANGER']->refresh_db_connection($new_connection);
    }

    if($obj & MATERIALS_MANGER_FLAG)
    {
        if(!isset($_SESSION['MATERIALS_MANGER']))
            $_SESSION['MATERIALS_MANGER'] = new MaterialsManger($new_connection);
        else 
           $_SESSION['MATERIALS_MANGER']->refresh_db_connection($new_connection);
    }

    if($obj & CLIENTS_MANGER_FLAG)
    {
        if(!isset($_SESSION['CLIENTS_MANGER']))
            $_SESSION['CLIENTS_MANGER'] = new ClientsManger($new_connection);
        else 
           $_SESSION['CLIENTS_MANGER']->refresh_db_connection($new_connection);
    }
}