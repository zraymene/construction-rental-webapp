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

refresh_mangers(MATERIALS_MANGER_FLAG | CLIENTS_MANGER_FLAG,$db_connection);

if(!isset($_SESSION['admin']))   // Check if admin is already loged in 
{
    header("Location:../auth/login.php");
}

$info_msg = $error_msg = "";

function check_img($file , $is_edit = false , $old_img_name = "")
{
    if(empty($file['name']))
        return IMG_MAT_DEFAULT;

    $img_name = "";
    $img_ext = strtolower(pathinfo(basename($file["name"]),PATHINFO_EXTENSION));

    if(!$is_edit)
        $img_name = uniqid("material-",true) . "." . $img_ext;
    else {

        if(strcmp($old_img_name , IMG_MAT_DEFAULT) != 0)
        {
            if(!unlink("imgs/{$old_img_name}"))
                $error_msg =  "Error while deleting images!";
        }

        $old_img_name = explode("."  ,$old_img_name );
        $img_name .= "{$old_img_name[0]}.{$old_img_name[1]}." . $img_ext; 
       
    }

    $output_file = "imgs/" . $img_name;

    if(!getimagesize($file["tmp_name"])) {
        $error_msg = "File is not an image.";
        return IMG_MAT_DEFAULT;
    }

    if ($file["size"] > IMG_MAX_SIZE) {
        $error_msg = "Sorry, your file is too large.";
        return IMG_MAT_DEFAULT;
    }

    if($img_ext != "jpg" && $img_ext != "png" && $img_ext != "jpeg"){
        $error_msg = "Only JPG, JPEG, PNG files are allowed.";
        return IMG_MAT_DEFAULT;
    }

    if (!move_uploaded_file($file["tmp_name"], $output_file)) {
        $error_msg = "There was an error uploading your file.";
        return IMG_MAT_DEFAULT;
    }
    
    return $img_name;
}

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    switch($_POST['action_type'])
    {
        case "add":        
            $img_name = check_img($_FILES['mat_img'] );
            
            $mat = new Material();
            $mat->name          = $_POST['mat_name'];
            $mat->default_price = $_POST['mat_dprice'];
            $mat->is_free       = TRUE;
            $mat->list_clients  = array();
            $mat->image_path    = $img_name;
            
            if($_SESSION['MATERIALS_MANGER']->add($mat))
                $info_msg = "Material added usccesfully!";
            else
                $error_msg = "Error while adding new Material !";

            $mat = $img_ext = $img_name = $output_file = NULL;

            break;
        case "edit":
            
            $new_mat = new Material();
            $new_mat->id            = $_POST['id'];
            $new_mat->name          = $_POST['mat_name'];
            $new_mat->default_price = $_POST['mat_dprice'];
            
            if($_FILES['mat_img']['size'] != 0) 
            {
        
                $img_name = $_POST['mat_img_name'];

                if(!strcmp($img_name , IMG_MAT_DEFAULT))
                    $new_mat->image_path = check_img($_FILES['mat_img']);
                else
                    $new_mat->image_path = check_img($_FILES['mat_img'] , true ,$img_name);
            }

            if($_SESSION['MATERIALS_MANGER']->update($new_mat))
                $info_msg = "Material edited usccesfully!";
            else
                $error_msg = "Error while edited new Material !";

            $new_mat = NULL;

            break;
        case "delete":

            foreach($_POST['name_imgs'] as $img)
            {
                if(strcmp($img , IMG_MAT_DEFAULT))
                {
                   if(!unlink("imgs/".$img))
                       echo "Error while deleting images!";
                }
            }

            if(!$_SESSION['MATERIALS_MANGER']->delete($_POST['list_ids'],$_POST['num_ids']))
                $error_msg = "Error while deleting materials!";
            else
                $info_msg = "Materials deleted succesfully!";
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MATERIAL CONTROL PANEL</title>
    <script src="../../js/scripts.js"></script>
    <style>
        table img {
            width : 100px;
            height : 100px;
        }
    </style>

</head>
<body>
    <?php include("../header.php"); ?>

    <div class="content-wraper">
        <div class="container">
            <h1>Materials table:</h1>
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
                    <th>name</th>
                    <th>Default Price</th>
                    <th>Is free (Now)</th>
                    <th>Number of rents</th>
                    <th>Clients list</th>
                    <th>Image</th>
                </tr>
                <?php 
                    $start = 0;

                    if(isset($_GET['page_num']))
                        $start += ($_GET['page_num'] * NUMBER_ELEMENTS_PER_PAGE) + 1; 

                    $res = $_SESSION['MATERIALS_MANGER']->select_limit($start , $start + NUMBER_ELEMENTS_PER_PAGE);
                        
                    if( $res != NULL)
                    {
                        $is_free = $cl_html = "";
                        $clients_list;

                        while($row = $res->fetch_array())
                        {  
                            $clients_list = json_decode($row['list_clients']);

                            if($row['is_free'])
                            {
                                $checkbox = "";
                                $is_free = "YES";
                            }else {
                                $is_free = "NO";
                            }

                            if($clients_list != null){
                                $tmp_arr = array();

                                foreach($clients_list as $id) {
                                    if(array_key_exists($id,$tmp_arr))
                                        $tmp_arr[$id] += 1;
                                    else
                                        $tmp_arr[$id] = 1;
                                }

                                foreach($tmp_arr as $id => $num) {
                                    $client   = $_SESSION['CLIENTS_MANGER']->select_id($id);

                                    $cl_html .= "Client : {$client->first_name} {$client->last_name}, rented {$num} time <br>";
                                    
                                    $client = null;
                                }
                            }

                            echo "<tr>\n<td><input type=\"checkbox\"/></td>
                                    <td>{$row['id']}</td>
                                    <td>{$row['name']}</td>
                                    <td>{$row['default_price']}</td>
                                    <td> {$is_free} </td>
                                    <td>". count($clients_list) ."</td>
                                    <td> {$cl_html} </td>
                                    <td><img src='imgs/{$row['image_path']}' alt='{$row['image_path']}'></td>
                                </tr>
                                ";
                        }

                        $res->free_result();
                        $is_free = $cl_html = $clients_list = NULL;
                }
                ?>
            </table>
            <p class="page-index"><?php 
                    $total_pages  = round( ($_SESSION['CLIENTS_MANGER']->get_total_rows_count() / NUMBER_ELEMENTS_PER_PAGE) + 0.5);
                    $current_page = intval((isset($_GET['page_num']) ?  $_GET['page_num'] : "1"));
                    echo $current_page . " / " . $total_pages;
                ?></p>
            <div class="btns-wraper">
                <button class="btn" onclick=<?php echo "location.href='index.php?page_num=". ($current_page - 1) ."'"; ?> type="button" <?php echo ($current_page == 1) ? "disabled" : ""; ?> >Previous</button>
                <button class="btn" onclick=<?php echo "location.href='index.php?page_num=". ($current_page + 1) ."'"; ?> type="button" <?php echo ($current_page == $total_pages) ? "disabled" : ""; ?>>Next</button>
            </div>

            <div class="btns-wraper">
                <button type="button" onclick="toggle_display('edit_wraper');" class="btn">Edit</button>
                <button type="button" onclick="toggle_display('add_wraper');" class="btn">Add</button>
                <button type="button" onclick="delete_form_submit(1);"class="btn">Delete</button>
            </div>
        </div>

        <div id="add_wraper" class="popup-container" hidden>    
            <div class="container center" >    
                <h1>Add new Material:</h1>
                <form name="material_form" method="POST" action="index.php" onsubmit="verify_material_data(this);" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="add" />
                    <label for="name_field">Material name:</label><br>
                    <input name="mat_name" type="text" class="name_field input-field"> <br>
                    <label for="price_field">Default price:</label><br>
                    <input name="mat_dprice" type="text" class="price_field input-field" value="0"><br>
                    <label for="img_field">Image:</label><br>
                    <input name="mat_img" type="file" class="img_field" ><br>
                    <div class="btns-wraper">
                        <input type="submit" value="Add" class="btn">
                        <button type="button" onclick="toggle_display('add_wraper');" class="btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="edit_wraper" class="popup-container" hidden>
            <div class="container center" hidden>
                <h4>Edit material:</h4>
                <form name="auth_form" method="POST" action="#" onsubmit="material_edit_form_submit(this);" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="edit" />
                    <input type="hidden" name="id" value="0" />
                    <input type="hidden" name="mat_img_name" value="default.png"/>
                    <label for="name_field">New Material name:</label><br>
                    <input name="mat_name" type="text" class="name_field input-field"> <br>
                    <label for="price_field">New Default price:</label><br>
                    <input name="mat_dprice" type="text" class="price_field input-field"><br>
                    <label for="img_field">New Image:</label><br>
                    <input name="mat_img" type="file" class="img_field" ><br>
                    <div class="btns-wraper">
                        <input type="submit" value="Edit" class="btn">
                        <button type="button" onclick="toggle_display('edit_wraper');" class="btn">Cancel</button>
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