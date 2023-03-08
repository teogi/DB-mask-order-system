# DB-mask-order-system

## Prerequisites:
1. Please use XAMPP and start Apache and MySQL at `localhost`.
2. Copy the main files unto a directory(e.g. `htdocs`) under XAMPP Installation Directory (e.g. `c:\xampp\`)
3. use a browser to open the required file (`url:localhost/\<your directory\>`)

## The E-R Model Design
![HW1_E-R_model](https://user-images.githubusercontent.com/50168588/223702990-306f1843-5b18-47bf-b461-1c81405a3c59.png)

## Relational Model Design
![HW1_Relational](https://user-images.githubusercontent.com/50168588/223703194-d5cee5c3-a21a-44d8-9063-92efff3f0c5a.png)

## The main codes:
* *index.php*: default entry webpage, before register & login
  * supplementary functional PHP codes:
    * *login.php*
    * *register.php*
* *home.php*:after login
  * sub-pages:
    * *shop_manager.php*
    * *my_order.php*
    * *shop_order.php*
    * *logout.php* (functional, just for cleaning the login user's data and redirect to default index.php)
  * other supplementary codes:
    * *c_order.php*
    * *shop.php*
    * *finishedSelected.php*
    * *cancelSelected.php*
    * *make_order.php*
