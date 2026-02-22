# PurePHPServer: A Server Built Entirely On PHP

It is a humble, simple server, which is not intended for any serious use. It was created for educational purposes only

## Installation

You need to have php installed on your machine to run this server. Either xampp or php-cli is good enough, though if you go with php-cli you have to additionally install php-sockets, php-fileinfo, php-mbstring extensions. In the installation xampp is going to be used.

1. Go to htdocs folder inside the xampp
    ```bash
    cd ...\xampp\htdocs
    ```
2. Clone the repo
    ```bash
    git clone https://github.com/verg87/PurePHPServer.git
    ```

## Features

*   **HTTP Request/Response Handling:** It manually parses raw HTTP requests and constructs HTTP responses. GET and POST methods are supported
*   **Static File Serving:** It can serve various file types from the corresponding `public` directory, with the `Content-Type` being determined by the file's mime type.
*   **File Uploads:** The server can accept files sent via `POST` requests with `multipart/form-data` form. It checks the mime type of the uploaded file against a configurable list of allowed types. Uploaded files are temporarily stored in the `src/tmp` directory.
*   **Configurable Behavior:** Key server settings are externalized to a `.conf` file.
*   **Custom Error Handling:** It has built-in HTML pages for common HTTP errors like 404 (Not Found) and 406 (Not Acceptable).

## Run The Server

To run the server use this command:
```bash
php PurePHPServer\src\Server.php
```
