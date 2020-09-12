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

$info_msg = $error_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    switch($_POST['action_type'])
    {
        case "add":        
            $rent = new Rent();
            $rent->client_id     = $_POST['client_id'];
            $rent->material_id   = $_POST['material_id'];
            $rent->price         = $_POST['price'];
            $rent->creation_date = date("Y-m-d G:i:s");
            $rent->deadline_date = $_POST['deadline'];
            $rent->status        = 1;
            $rent->author_id     = $_SESSION['admin']->id;
            
            if(($res = $_SESSION['RENTS_MANGER']->add($rent))){

                // Add the new added rent id to Client's rents list
                $client   = $_SESSION['CLIENTS_MANGER']->select_id($rent->client_id );
                array_push($client->list_rents, $res); 
                $new_cl = new Client();
                $new_cl->id           = $client->id;
                $new_cl->number_rents = $client->number_rents + 1;
                $new_cl->list_rents   = $client->list_rents;
                $_SESSION['CLIENTS_MANGER']->update($new_cl);

                // Add client's id to material's clients list
                $material = $_SESSION['MATERIALS_MANGER']->select_id($rent->material_id);
                array_push($material->list_clients, $client->id); 
                $new_mat = new Material();
                $new_mat->id           = $material->id;
                $new_mat->number_rents = $material->number_rents + 1;
                $new_mat->list_clients = $material->list_clients;
                $new_mat->is_free      = FALSE;
           
                $_SESSION['MATERIALS_MANGER']->update($new_mat);

                $material = $client = null;

                $info_msg = LANG_R("RENTS_ADD_SUCCESS");
            }else
                $error_msg = LANG_R("RENTS_ADD_FAILURE");

            $rent = null;

            break;
        case "edit":
            
            $new_rent = new Rent();
            $new_rent->id            = $_POST['id'];
            $new_rent->price         = $_POST['price'];
            $new_rent->deadline_date = $_POST['deadline'];
            $new_rent->author_id     = $_SESSION['admin']->id;

            if($_POST["status"] != "0") {

                $material = $_SESSION['MATERIALS_MANGER']->select_id(intval($_POST["mat_id"]));

                $new_mat = new Material();
                $new_mat->id  = $material->id;

                if($_POST['status'] == "2") // Rent set to done then free material 
                {
                    $new_mat->is_free = TRUE;             
                    // If i put this out if statement it will give sql error 
                    // cause we are calling update function but updating nothing 
                    $_SESSION['MATERIALS_MANGER']->update($new_mat);    
                }
                else if($_POST['status'] == "1")  
                {         
                    $new_mat->is_free = FALSE;
                    $_SESSION['MATERIALS_MANGER']->update($new_mat);
                }

                
                $new_rent->status = $_POST["status"];

                $new_mat = null;
            }

            if($_SESSION['RENTS_MANGER']->update($new_rent))
                $info_msg = LANG_R("RENTS_EDIT_SUCCESS");
            else
                $error_msg = LANG_R("RENTS_EDIT_FAILURE");

            $new_rent = NULL;
           
            break;
        case "delete":

            if(!$_SESSION['RENTS_MANGER']->delete($_POST['list_ids'],$_POST['num_ids']))
                $error_msg = LANG_R("RENTS_DELETE_FAILURE");
            else{
                // Delete rent's id from client's rents list
                $client = $_SESSION['CLIENTS_MANGER']->select_id($_POST["clients_id"]);
                if (($key = array_search($_POST['list_ids'][0] , $client->list_rents)) !== false) {

                    unset($client->list_rents[$key]);

                    if(count($client->list_rents) == 0)
                        $client->list_rents = array();

                    $new_cl = new Client();
                    $new_cl->id           = $client->id;
                    $new_cl->number_rents = $client->number_rents - 1;
                    $new_cl->list_rents   = $client->list_rents;
                    $_SESSION['CLIENTS_MANGER']->update($new_cl);
                }

                // Delete client's id from material's clients list
                $material = $_SESSION['MATERIALS_MANGER']->select_id($_POST["materials_id"]);
                if (($key = array_search($client->id , $material->list_clients)) !== false) {

                    unset($material->list_clients[$key]);

                    if(count($material->list_clients) == 0)
                        $material->list_clients = array();
                        
                    $new_mat = new Material();
                    $new_mat->id           = $material->id;
                    $new_mat->number_rents = $material->number_rents - 1;
                    $new_mat->list_clients = $material->list_clients;
                    $new_mat->is_free      = TRUE;
                    $_SESSION['MATERIALS_MANGER']->update($new_mat);
                }

                $material = $client = null;
                
                $info_msg = LANG_R("RENTS_DELETE_SUCCESS");
            }
         
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo LANG("RENTS_PAGE_TITLE");?></title>
    <script src="../../js/scripts.js"></script>
    <link rel="icon" href=<?php echo "\"http://{$_SERVER['HTTP_HOST']}/css/dashboard.png\"";?> >
</head>
<body <?php echo "onload=\"lang_js('" . $_COOKIE[LANG_COOKIE_NAME] . "');\"";?>>
    <?php include("../header.php"); ?>

    <div class="content-wraper">
        <div class="container">
            <h1><?php echo LANG("RENTS_PAGE_TABLE_TITLE");?>:</h1>
            <?php 
                    $color = $msg = "";

                    if(empty($error_msg) && !empty($info_msg)) {
                        $color = "green";
                        $msg = $info_msg;
                    }else if(!empty($error_msg) && empty($info_msg)){
                        $color = "red";
                        $msg = $error_msg;
                    }

                    if(!empty($color))
                    {
                        echo "<div class=\"notfication-container notif-{$color}\">
                                <div class=\"notif-icon\">
                                    <img />
                                </div>
                                <div class=\"notif-msg\">
                                    <p>{$msg}</p>
                                </div>
                            </div>";
                    }
                ?>
            <table id="elements_table" border=1>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th><?php echo LANG("RENTS_PAGE_TABLE_CLIENT");?></th>
                    <th><?php echo LANG("MATERIAL");?></th>
                    <th><?php echo LANG("RENTS_PAGE_TABLE_PRICE");?></th>
                    <th><?php echo LANG("RENTS_PAGE_TABLE_DATE_CREATION");?></th>
                    <th><?php echo LANG("RENTS_PAGE_TABLE_DEADLINE");?></th>
                    <th><?php echo LANG("RENTS_PAGE_TABLE_AUTHOR");?></th>
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

                            $status = "";

                            switch($row['status'])
                            {
                                case 1:
                                    $status = "tabel-td-pending";
                                    break;
                                case 3:
                                    $status = "tabel-td-danger";
                                    break;
                            }

                            echo "<tr class='{$status}'>\n<td><input type=\"checkbox\"/></td>
                                    <td>{$row['id']}</td>
                                    <td id=\"{$client->id}\">{$client->first_name} {$client->last_name}</td>
                                    <td id=\"{$material->id}\">{$material->name}</td>
                                    <td>{$row['price']}</td>
                                    <td>{$row['creation_date']}</td>
                                    <td>{$row['deadline_date']}</td>
                                    <td>{$admin->username}</td>
                                </tr>
                                ";

                            $client = $material = $admin = null;

                        }

                        $res->free_result();
                }
                ?>
            </table>
            <p class="page-index"><?php 
                    $total_pages  = round( ($_SESSION['CLIENTS_MANGER']->get_total_rows_count() / NUMBER_ELEMENTS_PER_PAGE) + 0.5);
                    $current_page = intval((isset($_GET['page_num']) ?  $_GET['page_num'] : "1"));
                    echo $current_page . " / " . $total_pages;
                ?></p>
            <div class="btns-wraper">
                <button class="btn" onclick=<?php echo "location.href='index.php?page_num=". ($current_page - 1) ."'"; ?> type="button" <?php echo ($current_page == 1) ? "disabled" : ""; ?> ><?php LANG("BUTTON_PREV"); ?></button>
                <button class="btn" onclick=<?php echo "location.href='index.php?page_num=". ($current_page + 1) ."'"; ?> type="button" <?php echo ($current_page == $total_pages) ? "disabled" : ""; ?>><?php LANG("BUTTON_NEXT"); ?></button>
            </div>
            <div class="btns-wraper">
                <button type="button" onclick="toggle_display('edit_wraper');" class="btn"><?php LANG("BUTTON_EDIT"); ?></button>
                <button type="button" onclick="toggle_display('add_wraper');" class="btn"><?php LANG("BUTTON_ADD"); ?></button>
                <button type="button" onclick="delete_form_submit(2);"class="btn"><?php LANG("BUTTON_DELETE"); ?></button>
            </div>
        </div>

        <div id="add_wraper" class="popup-container" hidden>    
            <div class="container center" hidden>
                <h1><?php LANG("RENTS_PAGE_ADD_RENT"); ?></h1>
                <form name="material_form" method="POST" action="index.php" onsubmit="verify_rent_data(this);" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="add" />
                    <label for="price_field"><?php LANG("RENTS_PAGE_TABLE_PRICE"); ?>:</label><br>
                    <input name="price" type="number" class="price_field input-field" value="1" min="1" step="any"><br>
                    <label for="client_field"><?php LANG("RENTS_PAGE_TABLE_CLIENT"); ?>:</label><br>
                    <select id="clients" name="client_id" class="client_field input-field select-field">
                    <option value="0"><?php LANG("RENTS_PAGE_CHOOSE_CLIENT"); ?></option>
                        <?php
                            $res = $_SESSION['CLIENTS_MANGER']->select_limit(0 , 100);
                            
                            if( $res != NULL)
                            {
                                while($row = $res->fetch_array())
                                {  
                                    if($row["status"] != CLIENT_MAX_LATE_BAN)
                                        echo "<option value=\"{$row["id"]}\">{$row["first_name"]} {$row["last_name"]} | {$row["email"]}</option>";
                                }
                            }
                        ?>
                    </select><br>
                    <label for="material_field"><?php LANG("MATERIAL"); ?>:</label><br>
                    <select id="materials" name="material_id" class="material_field input-field select-field">
                        <option value="0"><?php LANG("RENTS_PAGE_CHOOSE_MATERIAL"); ?></option>
                        <?php
                            $res = $_SESSION['MATERIALS_MANGER']->select_limit(0 , 100);
                            
                            if( $res != NULL)
                            {
                                while($row = $res->fetch_array())
                                {  
                        
                                    if($row['is_free'])
                                        echo "<option value=\"{$row["id"]}\">{$row["name"]}</option>";
                                }
                            }
                        ?>
                    </select><br>
                    <label for="deadline_field"><?php LANG("RENTS_PAGE_TABLE_DEADLINE"); ?>:</label><br>
                    <input type="date" name="deadline" class="deadline_field input-field">
                    <div class="btns-wraper">
                        <input type="submit" value=<?php LANG_1("BUTTON_ADD"); ?> class="btn">
                        <button type="button" onclick="toggle_display('add_wraper');" class="btn"><?php LANG("BUTTON_CANCEL"); ?></button>
                    </div>
                </form>
            </div>
        </div>

        <div id="edit_wraper" class="popup-container" hidden>
            <div class="container center" hidden>
                <h1><?php LANG("RENTS_PAGE_EDIT_RENT"); ?></h1>
                <form name="auth_form" method="POST" action="#" onsubmit="rents_edit_form_submit(this);" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="edit" />
                    <input type="hidden" name="id" value="0" />
                    <input type="hidden" name="mat_id" value="0"/>
                    <label for="price_field"><?php LANG("RENTS_PAGE_TABLE_PRICE"); ?>:</label><br>
                    <input name="price" type="number" class="price_field input-field" min="1" step="any"><br>
                    <label for="deadline_field"><?php LANG("RENTS_PAGE_TABLE_DEADLINE"); ?>:</label><br>
                    <input type="date" name="deadline" class="deadline_field input-field"><br>
                    <label for="status_field"><?php LANG("RENTS_PAGE_EDIT_STATUS"); ?>:</label><br>
                    <select id="status_id" name="status" class="status_field input-field select-field">
                        <option value="0"><?php LANG("STATUS_EDIT"); ?></option>
                        <option value="2"><?php LANG("STATUS_DONE"); ?></option>
                        <option value="1"><?php LANG("STATUS_PENDING"); ?></option>
                        <option value="3"><?php LANG("STATUS_LATE_DELIVERY"); ?></option>
                    </select><br>
                    <div class="btns-wraper">
                        <input type="submit" value=<?php LANG_1("BUTTON_EDIT"); ?> class="btn">
                        <button type="button" onclick="toggle_display('edit_wraper');" class="btn"><?php LANG("BUTTON_CANCEL"); ?></button>
                    </div>
                </form>
            </div>
        </div>


        <form name="delete_form" method="POST" action="index.php">
            <input type="hidden" name="action_type" value="delete" />
            <input type="hidden" name="num_ids" value="delete" />
        </form>

    </div>
</body>
</html>