

<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */


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

    // Returns wanted object , NULL on failure
    public function select_id($id)
    {
        $ps = $this->db_connection->prepare($this->select_id_query);

        $ps->bind_param("i",$id);

        if(!$ps->execute())
        {
            $ps->close();
            return NULL;
        }

        $res = $ps->get_result()->fetch_assoc();

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

/*
CREATE TABLE `materials` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `default_price` double unsigned DEFAULT 0,
 `is_free` tinyint(1) NOT NULL DEFAULT 0,
 `list_clients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`list_clients`)),
 `image_path` varchar(255) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8
*/
class MaterialsManger extends AbstractManger
{

    public function __construct($dbconnection)
    {
        parent::__construct($dbconnection);

        $this->add_query       = "INSERT INTO `materials` ( `name`, `default_price`, `is_free`, `list_clients`, `image_path`) VALUES ( ?, ?, ?, ?, ?);";
        $this->delete_id_query = "DELETE FROM `materials` WHERE `id` = ?";
        $this->select_id_query = "SELECT * FROM `materials` WHERE `id` = ?";
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

/*
CREATE TABLE `clients` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `first_name` varchar(255) NOT NULL,
 `last_name` varchar(255) NOT NULL,
 `email` varchar(255) NOT NULL,
 `phone` varchar(20) NOT NULL,
 `list_rents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8    
*/
class ClientsManger extends AbstractManger
{

    public function __construct($dbconnection)
    {
        parent::__construct($dbconnection);

        $this->add_query       = "INSERT INTO `clients` ( `first_name`, `last_name` , `email`, `phone`, `list_rents`) VALUES ( ?, ?, ?, ?, ?);";
        $this->delete_id_query = "DELETE FROM `clients` WHERE `id` = ?";
        $this->select_id_query = "SELECT * FROM `clients` WHERE `id` = ?";
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

/*
CREATE TABLE `rents` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_id` int(11) NOT NULL,
 `material_id` int(11) NOT NULL,
 `price` double NOT NULL DEFAULT 0,
 `creation_date` datetime NOT NULL,
 `deadline_date` date NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8
*/
class RentsManger extends AbstractManger
{
    public function __construct($dbconnection)
    {
        parent::__construct($dbconnection);

        $this->add_query       = "INSERT INTO `rents` ( `client_id`, `material_id`, `price`, `creation_date`, `deadline_date`) VALUES ( ?, ?, ?, ?, ?);";
        $this->delete_id_query = "DELETE FROM `rents` WHERE `id` = ?";
        $this->select_id_query = "SELECT * FROM `rents` WHERE `id` = ?";
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

        return $obj;
    }

    protected function bind_param($ps, $obj)
    {
        $ps->bind_param("iidss",
                    $obj->client_id,
                    $obj->material_id,
                    $obj->price,
                    $obj->creation_date,
                    $obj->deadline);

    }
}
