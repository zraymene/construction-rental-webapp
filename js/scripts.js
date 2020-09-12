/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */

var MSG_UP_EMPTY = "Username/Password is empty !";
var MSG_CHECKBOX_EMPTY = "You must pick a row !";
var MSG_CHECKBOX_MULTI = "You must pick only one row !";
var MSG_FIELDS_EMPTY = "You must fill those fields !";
var MSG_CONFIRM_DELETION = "Click Yes to confirm the deletion :";
var MSG_CONFIRM_UNBAN = "Click Yes to confirm the unban process :";

var MSG_RENTINFO_EMPTY = "Rent info is undefined !";
var MSG_CLIENTINFO_EMPTY = "Client info is undefined !";
var MSG_MATINFO_EMPTY = "Material name is undefined !";

function check_empty(str)
{
    if( str.trim() == null || str.trim() == ""|| str === " ")
        return true;

    return false;
}

function verify_data(wanted_form)
{

    if ( check_empty(wanted_form["username"].value) || check_empty(wanted_form["password"].value)) {
        alert(MSG_UP_EMPTY);
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
        mat_img_name : ""
    };

    for(var i = 0; i < checkboxes.length ; i++)
    {
        if(checkboxes[i].checked )
        {
            if(counter == 0)        // Pick the first one and wish the user didn't check more then 1 
            {
                obj.id           = checkboxes[i].parentNode.parentNode.cells[1].innerHTML;
                obj.mat_img_name = checkboxes[i].parentNode.parentNode.cells[6].childNodes[0].alt;

            }
            counter++;
        }
    }

    if(counter == 0)
    {
        alert(MSG_CHECKBOX_EMPTY);
        event.preventDefault();
        return;
    }

    if(counter > 1)                  // Great , he picked more then 1 
    {
        alert(MSG_CHECKBOX_MULTI);
        event.preventDefault();
        return;
    }

    var mat_name   = edit_form['mat_name'].value;

    if ( check_empty(mat_name) && edit_form['mat_img'].files.length == 0) 
    {
        alert(MSG_FIELDS_EMPTY);
        event.preventDefault();
        return;
    }
    
    edit_form['id'].value           = obj.id;
    edit_form["mat_img_name"].value = obj.mat_img_name;
}

function admin_edit_form_submit(edit_form)

{

    var checkboxes  = document.getElementById('elements_table').getElementsByTagName('INPUT');

    var counter = 0;
    var id;

    for(var i = 0; i < checkboxes.length ; i++)
    {
        if(checkboxes[i].checked )
        {
            if(counter == 0)        // Pick the first one and wish the user didn't check more then 1 
            {
                id = checkboxes[i].parentNode.parentNode.cells[1].innerHTML;
            }
            counter++;
        }
    }

    if(counter == 0)
    {
        alert(MSG_CHECKBOX_EMPTY);
        event.preventDefault();
        return;
    }

    if(counter > 1)     // Great , he picked more then 1 
    {
        alert(MSG_CHECKBOX_MULTI);
        event.preventDefault();
        return;
    }

    var user = edit_form["username"].value;
    var pass = edit_form["password"].value;

    if ( check_empty(user) && check_empty(pass)) 
    {
        alert(MSG_FIELDS_EMPTY);
        event.preventDefault();
        return;
    }

    edit_form['id'].value = id;
}   

function rents_edit_form_submit(edit_form)
{
    var checkboxes  = document.getElementById('elements_table').getElementsByTagName('INPUT');

    var counter = 0;
    var rent_id;
    var mat_id;

    for(var i = 0; i < checkboxes.length ; i++)
    {
        if(checkboxes[i].checked )
        {
            if(counter == 0)        // Pick the first one and wish the user didn't check more then 1 
            {
                rent_id = checkboxes[i].parentNode.parentNode.cells[1].innerHTML;
                mat_id  = checkboxes[i].parentNode.parentNode.cells[3].id;
            }
            counter++;
        }
    }

    if(counter == 0)
    {
        alert(MSG_CHECKBOX_EMPTY);
        event.preventDefault();
        return;
    }

    if(counter > 1)     // Great , he picked more then 1 
    {
        alert(MSG_CHECKBOX_MULTI);
        event.preventDefault();
        return;
    }

    var price     = edit_form["price"].value;
    var deadline  = edit_form["deadline"].value;
    var status    = edit_form["status"].value;


    if ( check_empty(price) && check_empty(deadline) && status == 0) 
    {
        alert(MSG_FIELDS_EMPTY);
        event.preventDefault();
        return;
    }
 
    edit_form['id'].value = rent_id;
    edit_form['mat_id'].value = mat_id;
}

function clients_edit_form_submit(edit_form)
{
    var checkboxes  = document.getElementById('elements_table').getElementsByTagName('INPUT');

    var counter = 0;
    var client_id;

    for(var i = 0; i < checkboxes.length ; i++)
    {
        if(checkboxes[i].checked )
        {
            if(counter == 0)        // Pick the first one and wish the user didn't check more then 1 
            {
                client_id = checkboxes[i].parentNode.parentNode.cells[1].innerHTML;
            }
            counter++;
        }
    }

    if(counter == 0)
    {
        alert(MSG_CHECKBOX_EMPTY);
        event.preventDefault();
        return;
    }

    if(counter > 1)     // Great , he picked more then 1 
    {
        alert(MSG_CHECKBOX_MULTI);
        event.preventDefault();
        return;
    }

    var first_name = edit_form["first_name"].value;
    var last_name  = edit_form["last_name"].value;
    var email      = edit_form["email"].value;
    var phone      = edit_form["phone"].value;

    if ( check_empty(first_name) && check_empty(last_name) && check_empty(email) && check_empty(phone)) 
    {
        alert(MSG_FIELDS_EMPTY);
        event.preventDefault();
        return;
    }
    
    edit_form['id'].value = client_id;

}

// 1 : img posting , 2 : rents posting , 0 : None 
function delete_form_submit(post_type = 0)
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

            switch(post_type)
            {
                case 1 :
                    var new_img_element = document.createElement("input");

                    new_img_element.type = "hidden";
                    new_img_element.name = "name_imgs[]";
                    new_img_element.value = checkboxes[i].parentNode.parentNode.cells[6].childNodes[0].alt;
                    
                    delete_form.appendChild(new_img_element);

                    break;
                case 2 :
                    var new_cl_id_element = document.createElement("input");

                    new_cl_id_element.type = "hidden";
                    new_cl_id_element.name = "clients_ids[]";
                    new_cl_id_element.value = checkboxes[i].parentNode.parentNode.cells[2].id;

                    var new_mat_id_element = document.createElement("input");

                    new_mat_id_element.type = "hidden";
                    new_mat_id_element.name = "materials_ids[]";
                    new_mat_id_element.value = checkboxes[i].parentNode.parentNode.cells[3].id;

                    delete_form.appendChild(new_cl_id_element);
                    delete_form.appendChild(new_mat_id_element);

                    break;
            }   

            num_ids++;
        }
    }
    
    if(num_ids == 0)
    {
        alert(MSG_CHECKBOX_EMPTY);
        return;
    }

    delete_form['num_ids'].value  = num_ids;

    if(confirm(MSG_CONFIRM_DELETION))
        document.forms['delete_form'].submit();
}

function unban_form_submit()
{
    var checkboxes  = document.getElementById('elements_table').getElementsByTagName('INPUT');
    var unban_form = document.forms['unban_form'];
    var num     = 0;
    var client_id;

    for(var i = 0; i < checkboxes.length ; i++)
    {
        if(checkboxes[i].checked )
        {
            if(checkboxes[i].parentNode.parentNode.className == "tabel-td-banned") {
                client_id  = checkboxes[i].parentNode.parentNode.cells[1].innerHTML;
                num++;
            }
        }
    }
    
    if(num == 0)
    {
        alert(MSG_CHECKBOX_EMPTY);
        return;
    }

    if(num > 1)
    {
        alert(MSG_CHECKBOX_EMPTY);
        return;
    }

    unban_form['client_id'].value  = client_id;

    if(confirm(MSG_CONFIRM_UNBAN))
        unban_form.submit();
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

function verify_rent_data(add_form)
{
    if(check_empty(add_form["price"].value) || add_form["client_id"].value == "0" 
       || add_form["material_id"].value == "0" || check_empty(add_form["deadline"].value))
    {
        alert(MSG_RENTINFO_EMPTY);
        event.preventDefault();
    }

    
}

function verify_client_data(add_form)
{
    if(check_empty(add_form["first_name"].value) || check_empty(add_form["last_name"].value
       || check_empty(add_form["email"].value) || check_empty(add_form["phone"].value)
    ))
    {
        alert(MSG_CLIENTINFO_EMPTY);
        event.preventDefault();
    }
}
 
function verify_material_data(add_form)
{
    if(check_empty(add_form["mat_name"].value))
    {
        alert(MSG_MATINFO_EMPTY);
        event.preventDefault();
    }
}