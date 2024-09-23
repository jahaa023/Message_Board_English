Message Board website translated from Norwegian to English for English speakers. See Message_Board repository for Norwegian version. All future updates will be done on this version (the english one).

Requirements for running website:
- PHP server with PHP 8.3.10, in this case im using php-cgi.exe on a IIS server with a FastCGI module and a MIME type for WebP.
- MySQL server with database and tables that are in the sql folder.
- These folders: user_images, profile_images, groupchat_images and groupchat_background_images.
- You also need the img folder with all the files inside
- conn.php needs to have the correct username and password for the MySQL server
- php.ini needs to have the mysqli extension enabled
