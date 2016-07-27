# Doing the same as command mysql_secure_installation
UPDATE mysql.user SET Password=PASSWORD('Sex!@ud1t0r') WHERE User='root';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DELETE FROM mysql.user WHERE User='';
FLUSH PRIVILEGES;
