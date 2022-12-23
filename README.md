# seed-basic
This DATA-SEED Reporting Version


# What is DATA-SEED

DATA SEED is a simple web application that automatically creates User Interface and API for insert, update and delete operations. UI and API are automagically made available when creating database tables. Entity relations are also supported

✔ No need to rewrite CRUD (create, read, update, delete) UI when creating or changing tables structure

✔ API for CRUD operations is created with zero effort. Same when changing database structure

✔ You can create own applications with no code or just simple SQL

✔ Many features (reporting, alerts, REST pipes etc.) can be added when upgrading SEED

✔ It is FREE.



# Installation steps:

------------------------------------------------------------------
* ALLWAYS PROTECT YOUR SEED FOLDER WITH A PASSWORD!!!!
* ALLWAYS USE HTTPS!!!!
------------------------------------------------------------------

# Installation guide with print-screens: https://data-seed.tech/install.php
# Tutorial: https://data-seed.tech/tutorial.php



1. CREATE SEED FOLDER AND COPY FILES
Copy files into your public_html/seed folder.


How to identify your public_html folder:

If you are not sure about public_html folder create a php test page with the following content and place it in the root folder of your site
(ironically it should be exactly the public_html):

<?php
header("Content-Type: text/plain;charset=UTF-8");
print($_SERVER['DOCUMENT_ROOT']);
?>

Access this php page in order to check current web root folder. It will return something like /home/your_user_name/public_html, or something simillar.
You should consider the equivalent of public_html from this structure.


In the end your Seed folder should look like:
/public_html/seed
	/appCode.inc
	/app_reports.inc
	/entityEdit.php
	....





2. CREATE DATABASE TABLES
Open the database.sql file and run the content in your mySQL instance in the database where you want to install Seed.

The database should look like:
your_database
	your_tables
	seed_apps
	seed_menus
	seed_modules
	seed_nomenclatures
	other_tables_of_yours
	


DO NOT CHANGE seed_... TABLES! THIS WILL KILL THE SEED!



3. CONFIGURE ACCESS TO mySQL DATABASE
3.1. Identify your public_html folder. If you are not sure please check the "How to identify your public_html folder" section above.
It should be something like /home/your_user_name/public_html.
 
3.2. Create a folder 'config' outside of the public_html folder, so it cannot be accessed by public.
Inside the folder create a file for mySQL configuration (i.e. mysql_config.php).
Include here connection details:
$conn = new mysqli("host", "user", "password", "database");
$conn -> set_charset("utf8");

IMPORTANT! In order to let Seed work properly, you shoud use exactly the name $conn for connection, as shown above.
 
 
3.3. Call this page in your site php pages with: require_once '/home/your_user_name/config/mysql_config.php';


4. PROTECT YOUR FOLDER WITH PASSWORD
Of course, you can do more to protect your Seed folder. We will present the minimum protection


------------------------------------------------------------------
* ALLWAYS PROTECT YOUR SEED FOLDER WITH A PASSWORD!!!!
* ALLWAYS USE HTTPS!!!!
------------------------------------------------------------------

