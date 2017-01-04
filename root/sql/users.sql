# Adding default user for db connection and grant privileges
CREATE USER 'sexiauditor'@'localhost' IDENTIFIED BY 'Sex!@ud1t0r';
GRANT ALL PRIVILEGES ON sexiauditor.* TO 'sexiauditor'@'localhost';
CREATE USER 'api'@'localhost' IDENTIFIED BY 'Sex!@ud1t0rR0x';
GRANT SELECT ON sexiauditor.* TO 'api'@'localhost';
FLUSH PRIVILEGES;
