# DATABASE MODULE DEMO FOR PHP

Most software programs often have to access and transact with the database to perform at least RUD (Retrieve, Update, Delete) mass data manipulation operations.

And many programming languages offer built-in modules that connects, interacts, transfer, modify & exchange data to and from the database.

PHP has a PHP Data Objects PDO which is a built-in class that interacts with the database.

As PDO is structured as a class, it could be used directly by any calling objects that require database data transactions.

However, it would be better to be in control with your code and your software project by creating a facade Database class that has a PDO class and a PDO Statement.

In this way, we would not be overwhelmed by the numerous and large features of the PDO and we would only use what we need from the PDO methods and properties.

This simple software demo project demonstrates how to use PHP's PDO and PDOStatement to interact with the database.

The module is tested through PHPUnit test case that (although not recommended) connects to a MySQL server installed within the computer where this PHP module would run.

This demo contains the following:
* Database.php which is the database access module itself.
* DatabaseTest.php which are series of PHPUnit tests that describes how to use the Database.php.
* CreateFruitsTableInDemoDatabase.sql which contains SQL commands to create the `demo_database` and its `fruits` table that shall be used by the DatabaseTest.php to test the Database.php....AND
* database_demo.yaml which is the configuration file that contains user credentials and server settings used to connect to the test MySQL server.

This PHP software demo on database access shall be compared with its corresponding Python and Raku Perl software demo.
