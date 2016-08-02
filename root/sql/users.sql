# Adding default user for db connection and grant privileges
CREATE USER 'sexiauditor'@'localhost' IDENTIFIED BY 'Sex!@ud1t0r';
GRANT ALL PRIVILEGES ON sexiauditor.* TO 'sexiauditor'@'localhost';
FLUSH PRIVILEGES;
