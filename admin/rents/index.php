<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 * 
 * In this page : 
 *  - View/Add/Edit/Remove other Materials
 * 
 */
require("../core/db_connect.php");
require("../core/systems.php");

session_start();

refresh_mangers(RENTS_MANGER_FLAG | CLIENTS_MANGER_FLAG | MATERIALS_MANGER_FLAG | ADMINS_MANGER_FLAG,$db_connection);

if(!isset($_SESSION['admin']))   // Check if admin is already loged in 
{
    header("Location:../auth/login.php");
}

$info = "";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    switch($_POST['action_type'])
    {
        case "add":        
            
            
            break;
        case "edit":

            $new_rent = new Rent();
            $new_rent->id         = $_POST['id'];
            $new_rent->first_name = $_POST['first_name'];
            $new_rent->last_name  = $_POST['last_name'];
            $new_rent->email      = $_POST['email'];
            $new_rent->phone      = $_POST['phone'];

            if($_SESSION['RENTS_MANGER']->update($new_rent))
                $info = "Client edited usccesfully!";
            else
                $info = "Error while editing Client !";

            $new_rent = NULL;
           
            break;
        case "delete":

            if(!$_SESSION['RENTS_MANGER']->delete($_POST['list_ids'],$_POST['num_ids']))
                $info = "Error while deleting clients!";
            else
                $info = "Client deleted succesfully!";
         
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RENTS CONTROL PANEL</title>
    <script src="../../js/scripts.js"></script>
    <style>
        img {
            width : 100px;
            height : 100px;
        }
    </style>
</head>
<body>
    <div id="global_wraper" style="width:300px;margin:auto;">
        <h1>Rents table:</h1>
        <ul>
            <li><a href="#">Rents</a></li>
            <li><a href="../materials/">Materials</a></li>
            <li><a href="../clients/">Clients</a></li>
            <li><a href="../">Admins</a></li>
        </ul>
        
        <hr>

        <p><?php echo $info?></p>

        <h1>Add new Rent:</h1>
        <form name="material_form" method="POST" action="index.php" onsubmit="verify_client_data(this);" enctype="multipart/form-data">
            <input type="hidden" name="action_type" value="add" />
  
            <label for="price_field">Price :</label><br>
            <input name="price" type="number" class="price_field" ><br>

            <label for="client_field">Client :</label><br>
            <input list="clients" name="client_id" class="client_field">
            <datalist id="clients">
                <option value="Edge">
                <option value="Firefox">
            </datalist><br>

            <label for="material_field">Material :</label><br>
            <input list="materials" name="material_id" class="material_field">
            <datalist id="materials">
                <option value="Edge">
                <option value="Firefox">
            </datalist><br>

            <label for="deadline_field">Deadline :</label><br>
            <input type="date" name="deadline" class="deadline_field">
            <input type="submit" value="Add" class="submit_btn">
        </form>

        <hr>

        <h1>Rents table:</h1>
        <table id="elements_table" border=1>
            <tr>
                <th></th>
                <th>ID</th>
                <th>Client</th>
                <th>Material</th>
                <th>Price</th>
                <th>Creation date</th>
                <th>Deadline</th>
                <th>Author</th>
            </tr>
            <?php 
                $start = 0;
                
                if(isset($_GET['page_num']))
                    $start += ($_GET['page_num'] * NUMBER_ELEMENTS_PER_PAGE) + 1; 

                $res = $_SESSION['RENTS_MANGER']->select_limit($start , $start + NUMBER_ELEMENTS_PER_PAGE);
                    
                if( $res != NULL)
                {
                    $client;
                    $material;
                    $admin;

                    while($row = $res->fetch_array())
                    {  
                        $place_holder = "";

                        if( ($client   = $_SESSION['CLIENTS_MANGER']->select_id($row['client_id'])) == null) {
                            $client = new Client();
                            $client->id         = 0;
                            $client->first_name = "NOT";
                            $client->last_name  = "FOUND";
                        }

                        if( ($material = $_SESSION['MATERIALS_MANGER']->select_id($row['material_id'])) == null)
                        {
                            $material = new Material();
                            $material->id   = 0;
                            $material->name = "NOT FOUND";
                        }

                        if( ($admin = $_SESSION['ADMINS_MANGER']->select_id($row['author_id'])) == null)
                        {
                            $material = new Material();
                            $admin->name = "NOT FOUND";
                        }

                        echo "<tr>\n<td><input type=\"checkbox\"/></td>
                                <td>{$row['id']}</td>
                                <td id=\"{$client->id}\">{{$client->first_name} {$client->last_name}</td>
                                <td id=\"{$material->id}\">{{$material->name}</td>
                                <td>{$row['price']}</td>
                                <td>{$row['creation_date']}</td>
                                <td>{$row['deadline_date']}</td>
                                <td>{$admin->name}</td>
                            </tr>
                            ";

                        $client = $material = $admin = null;

                    }

                    $res->free_result();
            }
            ?>
        </table><br>
        <p><?php 

                echo (isset($_GET['page_num']) ?  $_GET['page_num'] : "1"). "/" . round( ($_SESSION['CLIENTS_MANGER']->get_total_rows_count() / NUMBER_ELEMENTS_PER_PAGE) + 0.5);
            ?></p>
        <button type="button" onclick="toggle_display('edit_wraper');">Edit</button>
        <button type="button" onclick="delete_form_submit();">Delete</button>

        <div id="edit_wraper" hidden>
            <h4>Edit clients:</h4>
            <form name="auth_form" method="POST" action="#" onsubmit="clients_edit_form_submit(this);" enctype="multipart/form-data">
                <input type="hidden" name="action_type" value="edit" />
                <input type="hidden" name="id" value="0" />

                <label for="price_field">Price :</label><br>
                <input name="price" type="number" class="price_field" ><br>

                <label for="client_field">Client :</label><br>
                <input list="clients" name="client_id" class="client_field">
                <datalist id="clients">
                    <option value="Edge">
                    <option value="Firefox">
                </datalist><br>

                <label for="material_field">Material :</label><br>
                <input list="materials" name="material_id" class="material_field">
                <datalist id="materials">
                    <option value="Edge">
                    <option value="Firefox">
                </datalist><br>

                <label for="deadline_field">Deadline :</label><br>
                <input type="date" name="deadline" class="deadline_field">
                <input type="submit" value="Add" class="submit_btn">
            </form>
        </div>
        <hr>

        <form name="delete_form" method="POST" action="index.php">
            <input type="hidden" name="action_type" value="delete" />
            <input type="hidden" name="num_ids" value="delete" />
        </form>


        <form method="POST" action="../auth/logout.php">
            <input type="submit" value="Logout" class="submit_btn">
        </form>
    </div>
</body>
</html>