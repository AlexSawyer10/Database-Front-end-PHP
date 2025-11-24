# Database Front-end

## How To Run The App With Docker?
To run this app in docker, first you would need to run your docker container. Then, you will want to go to your terminal and start your container in your terminal with the command : docker start -ai mysql-container. Next, you are going to want to start your sql service with the command: systemctl start mysql.service.
After starting your service, confirm that it's running with systemctl status mysql.

##  What Database Tables Are Used?
In this web application, you can manually change data in two tables. The first one is the product table which consists of a product code, description, indate, quantity on hand, min, price, discount, and a foreign key to the vendor table.
The other table is the vendor table. The vendor table consists of a vendor code, name, contract, area code, phone, state, and order. The other table the user indirectly adds to is that User table that I manually created. The user table consists of an id that autoincrements, a username, and a password which is hashed in the database.

## Features Implemented

* Registration and login abilities
* Password hashing
* Password constraints (Requiring more complex passwords)
* Product table manipulation through the UI
* Vendor table manipulation through the UI
* Authenticated dashboard
* Nav
* Guest viewership of both product and vendor tables
* Guest dashboard
* SQL Injection prevention
* Cross-side scripting prevention

## Technologies Used
* PHP
* HTML
* CSS
* BOOTSTRAP
* Docker
* MySQL
