-- Not mounted by docker-compose.yml. MySQL creates the database from MYSQL_DATABASE (.env);
-- Laravel migrations create the tables.
USE studentdb;
SELECT 'Database ready for Laravel!' AS status;
