/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */

 var global_var;

function check_empty(str)
{
    if( str.trim() == null || str.trim() == ""|| str === " ")
        return true;

    return false;
}

function verify_data(wanted_form)
{

    if ( check_empty(wanted_form["username"].value) || check_empty(wanted_form["password"].value)) {
        alert("Username/Password is empty !");
        event.preventDefault();
        return false;
    }

    return true;
}

function material_edit_form_submit(edit_form)
{
    var checkboxes  = document.getElementById('elements_table').getElementsByTagName('INPUT');

    var counter = 0;
    var obj = {
        id         : 0,
        mat_name   : "",
        mat_dprice : "",
        mat_img_name : ""
    };

    for(var i = 0; i < checkboxes.length ; i++)
    {
        if(checkboxes[i].checked )
        {
            if(counter == 0)        // Pick the first one and wish the user didn't check more then 1 
            {
                obj.id           = checkboxes[i].parentNode.parentNode.cells[1].innerHTML;
                obj.mat_name     = checkboxes[i].parentNode.parentNode.cells[2].innerHTML;
                obj.mat_dprice   = checkboxes[i].parentNode.parentNode.cells[3].innerHTML; 
                obj.mat_img_name = checkboxes[i].parentNode.parentNode.cells[7].childNodes[0].alt;

            }
            counter++;
        }
    }

    if(counter == 0)
    {
        alert("Pick a row ... Faggot !");
        event.preventDefault();
        return;
    }

    if(counter > 1)                  // Great , he picked more then 1 
    {
        alert("Pick only one ... Faggot !");
        event.preventDefault();
        return;
    }

    var mat_name   = edit_form['mat_name'].value;
    var mat_dprice = edit_form['mat_dprice'].value;

    console.log(edit_form['mat_img'].files.length);

    if ( check_empty(mat_name) && check_empty(mat_dprice) && edit_form['mat_img'].files.length == 0) 
    {
        alert("Those fields won't fill on thier own !");
        event.preventDefault();
        return;
    }
    
    if(!check_empty(mat_name))
        obj.mat_name = mat_name;
    
    if(!check_empty(mat_dprice))
        obj.mat_dprice = mat_dprice;

    edit_form['id'].value           = obj.id;
    edit_form["mat_name"].value     = obj.mat_name;
    edit_form["mat_dprice"].value   = obj.mat_dprice;
    edit_form["mat_img_name"].value = obj.mat_img_name;

    console.log(obj);
   //event.preventDefault();
}

function admin_edit_form_submit(edit_form)

{

    var checkboxes  = document.getElementById('elements_table').getElementsByTagName('INPUT');

    var counter = 0;
    var obj = {
        id : 0,
        username : "",
        password : ""
    };

    for(var i = 0; i < checkboxes.length ; i++)
    {
        if(checkboxes[i].checked )
        {
            if(counter == 0)        // Pick the first one and wish the user didn't check more then 1 
            {
                obj.id       = checkboxes[i].parentNode.parentNode.cells[1].innerHTML;
                obj.username = checkboxes[i].parentNode.parentNode.cells[2].innerHTML;
                obj.password = checkboxes[i].parentNode.parentNode.cells[3].innerHTML;
            }
            counter++;
        }
    }

    if(counter == 0)
    {
        alert("Pick a row ... Faggot !");
        event.preventDefault();
        return;
    }

    if(counter > 1)     // Great , he picked more then 1 
    {
        alert("Pick only one ... Faggot !");
        event.preventDefault();
        return;
    }

    var user = edit_form["username"].value;
    var pass = edit_form["password"].value;

    if ( check_empty(user) && check_empty(pass)) 
    {
        alert("Those fields won't fill on thier own !");
        event.preventDefault();
        return;
    }
    
    if(!check_empty(user) && check_empty(pass))
        obj.username = user;
    else if(check_empty(user) && !check_empty(pass))
        obj.password = pass;

    edit_form['id'].value       = obj.id;
    edit_form["username"].value = obj.username;
    edit_form["password"].value = obj.password;
}   

function delete_form_submit(is_imgs_included = false)
{
    var checkboxes  = document.getElementById('elements_table').getElementsByTagName('INPUT');
    var delete_form = document.forms['delete_form'];
    var num_ids     = 0;

    for(var i = 0; i < checkboxes.length ; i++)
    {
        if(checkboxes[i].checked )
        {
            var new_element = document.createElement("input");

            new_element.type = "hidden";
            new_element.name = "list_ids[]";
            new_element.value = checkboxes[i].parentNode.parentNode.cells[1].innerHTML;
            
            delete_form.appendChild(new_element);

            if(is_imgs_included)
            {
                var new_img_element = document.createElement("input");

                new_img_element.type = "hidden";
                new_img_element.name = "name_imgs[]";
                new_img_element.value = checkboxes[i].parentNode.parentNode.cells[7].childNodes[0].alt;
                
                delete_form.appendChild(new_img_element);
            }

            num_ids++;
        }
    }

    delete_form['num_ids'].value  = num_ids;

    if(confirm("Click yes to confirm the deletion :"))
        document.forms['delete_form'].submit();
}

function toggle_display(val)
{
    var x = document.getElementById(val);
    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
  }
}
 
function verify_material_data(add_form)
{
    if(check_empty(add_form["mat_name"].value))
    {
        alert("Material name is undefined !");
        event.preventDefault();
    }
}