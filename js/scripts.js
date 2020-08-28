/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */

function verify_data()
    {
        var x = document.forms["auth_form"]["username"].value;
         //   var y = document.forms["reg_form"]["password"].value;

        if ( (x.trim()==null || x.trim()==""|| x===" ") ) {
            alert("Username is empty !");
            event.preventDefault();
        }
}