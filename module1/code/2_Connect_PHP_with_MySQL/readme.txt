What is this folder?
====================

These files accompany the "Connect PHP with MySQL" slides. The goal is CRUD:
PHP contains the application logic, PDO connects PHP to MySQL, and MySQL keeps
the persistent data.

Before running the PHP examples
-------------------------------
1. Start the MySQL service.
   - WSL2/Ubuntu: sudo service mysql start
   - macOS: brew services start mysql
2. Follow command.txt to create studentdb, the students table, and the local
   ase230 database account.
3. Start the PHP development server from this folder:
       php -S localhost:8000

Required learning path
----------------------
1. Open http://localhost:8000/index_pdo.php
   - Makes the first PDO connection and inserts one student.
   - Corresponds to "2. Simple_PHP_Server_with_MySQL.md".
2. Open http://localhost:8000/crud_pdo.php
   - Runs Create, Read, Update, and Delete in one file.
   - Corresponds to "3. CRUD_operation_using_PDO.md".

The two PHP files are independent runs. An ID created in index_pdo.php is not
passed to crud_pdo.php. Each file obtains the ID of the row it creates.

Running crud_pdo.php more than once
-----------------------------------
Every run creates a new Alice and then updates that Alice. Previous Alice rows
remain because MySQL data is persistent. This is expected, not an error.

If you need a clean table for another classroom demonstration, use the optional
reset command documented at the end of command.txt. That command deletes all
students, so run it only when you intentionally want to remove the demo data.

Optional MySQLi reference
-------------------------
- insert_mysqli_optional.php shows the first INSERT using MySQLi.
- crud_mysqli_optional.php shows CRUD using MySQLi.

MySQLi is not part of the required learning path. Use these files only when
comparing syntax or reading an older PHP project.

If something goes wrong
------------------------
Check the Optional troubleshooting slides first. Computer configurations vary,
so if the issue continues, contact or visit the professor and we will work
through it together.
