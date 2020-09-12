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
    var $num_rent;          // Rents IDs
    var $list_rents;
    var $status;            // Number of late deliveries + 1 , 1 clean
}

class Material 
{
    var $id;
    var $name;
    var $num_rents;
    var $is_free;
    var $list_clients;      // Clients IDs
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
    var $status;            // 1 : pending , 2 : done , 3 : passed deadline
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
    protected $select_range_query;
    protected $update_id_query;
    protected $count_total_rows_query;

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

    public function get_total_rows_count()
    {
        if( ($res = $this->db_connection->query($this->count_total_rows_query)) == null )
        {
            echo $this->db_connection->error;
            return 0;
        }

        return $res->fetch_assoc()['total'];

    }

    // Returns result table ; NULL on failure
    // I had to not use prepared statment because they seems don't work with LIMIT 
    public function select_limit($offset, $count)
    {
        $query = $this->select_range_query . "{$offset},{$count}";

        if(!($ps = $this->db_connection->query($query))) 
        {
            echo $this->db_connection->error;
            return NULL;
        }
   
        if($ps->num_rows <= 0 )
        {
            echo $this->db_connection->error;
            return NULL;
        }

        return $ps;
    }

    // Returns wanted object , NULL on failure
    public function select_id($id)
    {
        $ps = $this->db_connection->prepare($this->select_id_query);

        if(!$ps)
        {
            echo $this->db_connection->error;
            return NULL;
        }

        $ps->bind_param("i",$id);

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

        $ps->close();

        return $this->create_object($res);
    }

    // Returns new db generated id on success ; NULL on failure
    public function add($obj)
    {
        $ps = $this->db_connection->prepare($this->add_query);
        
        if(!$ps)
        {
            echo $this->db_connection->error;
            return NULL;
        }


        $this->bind_param($ps, $obj);

        if(!$ps->execute())
        {
            echo $this->db_connection->error;
            return NULL;
        }
        
        $return = $ps->insert_id;
    
        $ps->close();

        return $return;
    }

    // Returns true on succes ; FALSE/NULL in failure
    // Now it doesn't update whole object fields 
    // Update only set variables on $new_obj 
    // id field is needed and it must be set !!
    public function update($new_obj)
    {   
        $query = $this->update_id_query;
        $arr = array();
        $bind_param_str = "";
        $count = 0;

        foreach ($new_obj as $var => $val) {

            if( !empty($val) || gettype($val) == "array" || ( isset($val) && !empty($val) || is_bool($val) )) {      // IDK how it did work tho
                if($var != "id" )    // I was forced to add this condition alone , because it won't work if it is in the IF above
                {                
                        switch(gettype($val))
                        {
                            case "integer":
                                $bind_param_str .= "i";
                                break;
                            case "double":
                                $bind_param_str .= "d";
                                break;
                            case "string":
                                $bind_param_str .= "s";
                                break;
                            case "boolean":
                                $bind_param_str .= "i";
                                break;
                            case "array":
                                $bind_param_str .= "s";
                                $val = json_encode($val);
                                break;

                        }

                        if($count == 0)
                            $query .= "`{$var}` = ?";
                        else
                            $query .= ",`{$var}` = ?";
                        
                        array_push($arr,$val);
                        $count++;   
                }
            }
        }

        $query          .= " WHERE `id` = ?";
        $bind_param_str .= "i";

        array_push($arr , $new_obj->id);

       // echo "<br>". $query;

        if(!($ps = $this->db_connection->prepare($query)))
        {
            echo $this->db_connection->error;
            return NULL;
        }

        $ps->bind_param($bind_param_str , ...$arr);

        if(!$ps->execute())
        {
            echo $this->db_connection->error;
            return NULL;
        }

        $ps->close();


        return TRUE;
    }

    //Pass single id or array if ids Returns number of effected rows , 0 on failure
    public function delete($id ,$size = 1)
    {
        $new_query = $this->delete_id_query;
        $bind_param_str = "i";

        if($size != 1)
        {
            $new_query .= "IN (?";
            
            for($i = 1 ; $i < $size ; $i++)
            {
                $new_query      .= ",?";
                $bind_param_str .= "i";
            }
            $new_query .= ")";

            $id = array_map('intval', $id);

        }else
            $new_query .= "= ?";

        $ps = $this->db_connection->prepare($new_query);

        if(!$ps)
        {
            echo $this->db_connection->error;
            return NULL;
        }

        $ps->bind_param($bind_param_str,...$id);

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

        $this->add_query              = "INSERT INTO ". DATABASE_NAME .".`materials` ( `name`, `is_free`, `list_clients`, `image_path`) VALUES ( ?, ?, ?, ?);";
        $this->delete_id_query        = "DELETE FROM ". DATABASE_NAME .".`materials` WHERE `id` ";
        $this->select_id_query        = "SELECT * FROM ". DATABASE_NAME .".`materials` WHERE `id` = ?";
        $this->select_range_query     = "SELECT * FROM ". DATABASE_NAME .".`materials` LIMIT ";
        $this->update_id_query        = "UPDATE ". DATABASE_NAME .".`materials` SET ";
        $this->count_total_rows_query = "SELECT count(*) AS total FROM ". DATABASE_NAME .".`materials`";
    }

    protected function create_object($res)
    {
        $obj = new Material();

        $obj->id            = $res['id'];
        $obj->name          = $res['name'];
        $obj->is_free       = $res['is_free'];
        $obj->list_clients  = json_decode($res['list_clients']);
        $obj->num_rents     = count($obj->list_clients);

        return $obj;
    }

    protected function bind_param($ps, $obj)
    {
        $json_str = json_encode($obj->list_clients);  // To prevent pass by refrence warning in bin_parm 

        $ps->bind_param("siss",
                    $obj->name,
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

        $this->add_query          = "INSERT INTO ". DATABASE_NAME .".`clients` ( `first_name`, `last_name` , `email`, `phone`, `status`, `list_rents`) VALUES ( ?, ?, ?, ?, ?, ?);";
        $this->delete_id_query    = "DELETE FROM ". DATABASE_NAME .".`clients` WHERE `id` ";
        $this->select_id_query    = "SELECT * FROM ". DATABASE_NAME .".`clients` WHERE `id` = ?";
        $this->select_range_query = "SELECT * FROM ". DATABASE_NAME .".`clients` LIMIT ";
        $this->update_id_query    = "UPDATE ". DATABASE_NAME .".`clients` SET ";
        $this->count_total_rows_query = "SELECT count(*) AS total FROM ". DATABASE_NAME .".`clients`";
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
        $obj->status     = $res['status'];
        return $obj;
    }

    protected function bind_param($ps, $obj)
    {
        $json_str = json_encode($obj->list_rents);  // To prevent pass by refrence warning in bin_parm 

        $ps->bind_param('ssssis',
                    $obj->first_name,
                    $obj->last_name,
                    $obj->email,
                    $obj->phone,
                    $obj->status,
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

        $this->add_query          = "INSERT INTO ". DATABASE_NAME .".`rents` ( `client_id`, `material_id`, `price`, `creation_date`, `deadline_date`, `status`, `author_id`) VALUES ( ?, ?, ?, ?, ?, ?, ?);";
        $this->delete_id_query    = "DELETE FROM ". DATABASE_NAME .".`rents` WHERE `id` ";
        $this->select_id_query    = "SELECT * FROM ". DATABASE_NAME .".`rents` WHERE `id` = ?";
        $this->select_range_query = "SELECT * FROM ". DATABASE_NAME .".`rents` LIMIT ";
        $this->update_id_query    = "UPDATE ". DATABASE_NAME .".`rents` SET ";
        $this->count_total_rows_query = "SELECT count(*) AS total FROM ". DATABASE_NAME .".`rents`";
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
        $obj->status        = $res['status'];
        $obj->author_id     = $res['author_id'];

        return $obj;
    }

    protected function bind_param($ps, $obj)
    {
        $ps->bind_param("iidssii",
                    $obj->client_id,
                    $obj->material_id,
                    $obj->price,
                    $obj->creation_date,
                    $obj->deadline_date,
                    $obj->status,
                    $obj->author_id);
   
    }

    public function load_pending() 
    {
        $query = "SELECT `id`, `client_id`, `material_id` FROM ". DATABASE_NAME .".`rents` 
                        WHERE `status` = 1 AND `deadline_date` >= CURRENT_DATE()";
        
        if(!($res = $this->db_connection->query($query))) 
        {
            echo $this->db_connection->error;
            return NULL;
        }
   
        if($res->num_rows <= 0 )
        {
            echo $this->db_connection->error;
            return NULL;
        }

        return $res;
    }
}

class AdminsManger extends AbstractManger
{
    public function __construct($dbconnection)
    {
        parent::__construct($dbconnection);

        $this->add_query       = "INSERT INTO ". DATABASE_NAME .".`admins` ( `username`, `password`, `is_ceo`) VALUES ( ?, ?, ?);";
        $this->delete_id_query = "DELETE FROM ". DATABASE_NAME .".`admins` WHERE `id` ";
        $this->select_id_query = "SELECT * FROM ". DATABASE_NAME .".`admins` WHERE `id` = ?";
        $this->update_id_query = "UPDATE ". DATABASE_NAME .".`admins` SET ";
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
                    $obj->is_ceo
        );
        
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

    // Returns result table to fetch from ; Null on failure
    public function select_all()
    {
        $query = "SELECT * FROM ". DATABASE_NAME .".`admins`";

        $res = $this->db_connection->query($query);

        if($res->num_rows <= 0 || $res == NULL)
        {
            echo $this->db_connection->error;
            return NULL;
        }

        return $res;
    }

    // Retuns number of records found , -1 on failure
    public function record_count($username)
    {
        $auth_query = "SELECT `id` FROM ". DATABASE_NAME .".`admins` WHERE `username` = ?";

        if(!($ps = $this->db_connection->prepare($auth_query)))
        {
            echo $this->db_connection->error;
            return -1;
        }

        $ps->bind_param("s", $username);

        if(!$ps->execute())   
        {
            $ps->close();
            return -1;
        }

        $ps->store_result();

        return $ps->num_rows;

    }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////

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

function monitor_rents() 
{
 
    if( ($rents_list = $_SESSION["RENTS_MANGER"]->load_pending()) != NULL)
    {    
        while($rent = $rents_list->fetch_array())
        {
            $client   = $_SESSION["CLIENTS_MANGER"]->select_id($rent['client_id']);

            if(!$client)
                return false;

            $new_cl = new Client();
            $new_cl->id     = $client->id;
            $new_cl->status = $client->status + 1;      // Add 1 to client's penalty

            $_SESSION['CLIENTS_MANGER']->update($new_cl);

            $new_rent = new Rent();
            $new_rent->id = $rent['id'];
            $new_rent->status = 3;          // Set rent state to danger 

            $_SESSION['RENTS_MANGER']->update($new_rent);
            
        }

        $rents_list->free_result();
    }else 
        return false;


    return true;
}