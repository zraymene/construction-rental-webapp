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

$info = "";

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
                echo "Error while deleting images!";
        }

        $old_img_name = explode("."  ,$old_img_name );
        $img_name .= "{$old_img_name[0]}.{$old_img_name[1]}." . $img_ext; 
       
    }

    $output_file = "imgs/" . $img_name;

    if(!getimagesize($file["tmp_name"])) {
        $info = "File is not an image.";
        return IMG_MAT_DEFAULT;
    }

    if ($file["size"] > IMG_MAX_SIZE) {
        $info = "Sorry, your file is too large.";
        return IMG_MAT_DEFAULT;
    }

    if($img_ext != "jpg" && $img_ext != "png" && $img_ext != "jpeg"){
        $info = "Only JPG, JPEG, PNG files are allowed.";
        return IMG_MAT_DEFAULT;
    }

    if (!move_uploaded_file($file["tmp_name"], $output_file)) {
        $info = "There was an error uploading your file.";
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
                $info = "Material added usccesfully!";
            else
                $info = "Error while adding new Material !";

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
                $info = "Material edited usccesfully!";
            else
                $info = "Error while edited new Material !";

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
                $info = "Error while deleting materials!";
            else
                $info = "Materials deleted succesfully!";
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
        img {
            width : 100px;
            height : 100px;
        }
    </style>
</head>
<body>
    <div id="global_wraper" style="width:300px;margin:auto;">
        <h1>Materials table:</h1>
        <ul>
            <li><a href="../rents/">Rents</a></li>
            <li><a href="#">Materials</a></li>
            <li><a href="../clients/">Clients</a></li>
            <li><a href="../">Admins</a></li>
        </ul>
        
        <hr>

        <p><?php echo $info?></p>

        <h1>Add new Material:</h1>
        <form name="material_form" method="POST" action="index.php" onsubmit="verify_material_data(this);" enctype="multipart/form-data">
            <input type="hidden" name="action_type" value="add" />
            <label for="name_field">Material name:</label><br>
            <input name="mat_name" type="text" class="name_field"> <br>
            <label for="price_field">Default price:</label><br>
            <input name="mat_dprice" type="text" class="price_field" value="0"><br>
            <label for="img_field">Image:</label><br>
            <input name="mat_img" type="file" class="img_field" ><br>
            <input type="submit" value="Add" class="submit_btn">
        </form>

        <hr>

        <h1>Materials table:</h1>
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
                            foreach($clients_list as $id => $num) {
                                $cl_html .= "Client {$id} rented {$num} time <br>";
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
        </table><br>
        <p><?php 

                echo (isset($_GET['page_num']) ?  $_GET['page_num'] : "1"). "/" . round( ($_SESSION['MATERIALS_MANGER']->get_total_rows_count() / NUMBER_ELEMENTS_PER_PAGE) + 0.5);
            ?></p>
        <button type="button" onclick="toggle_display('edit_admin_wraper');">Edit</button>
        <button type="button" onclick="delete_form_submit(true);">Delete</button>

        <div id="edit_admin_wraper" hidden>
            <h4>Edit material:</h4>
            <form name="auth_form" method="POST" action="#" onsubmit="material_edit_form_submit(this);" enctype="multipart/form-data">
                <input type="hidden" name="action_type" value="edit" />
                <input type="hidden" name="id" value="0" />
                <input type="hidden" name="mat_img_name" value="default.png"/>
                <label for="name_field">New Material name:</label><br>
                <input name="mat_name" type="text" class="name_field"> <br>
                <label for="price_field">New Default price:</label><br>
                <input name="mat_dprice" type="text" class="price_field"><br>
                <label for="img_field">New Image:</label><br>
                <input name="mat_img" type="file" class="img_field" ><br>
                <input type="submit" value="Edit" class="submit_btn">
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