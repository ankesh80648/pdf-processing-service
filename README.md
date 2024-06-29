# PDF Processing Service

This is a simple REST service for uploading and processing PDF files. The service extracts text from uploaded PDF files, counts the number of words, and returns the word count along with a unique identifier.

## Prerequisites

- PHP 7.4 or higher
- Composer
- Git

## Installation

1. Clone the repository:
   ```sh
   git clone https://github.com/<your_username>/<repository>.git
   cd <repository>
Install dependencies using Composer:

sh
Copy code
composer install
Start the Slim application:

sh
Copy code
php -S localhost:8080 -t public
Usage
Upload a PDF File
sh
Copy code
curl -X POST -F "pdf=@C:/path/to/your/file.pdf" http://localhost:8080/upload
Response:

json
Copy code
{"file_id":"<file_id>"}
Process the Uploaded PDF File
sh
Copy code
curl -X POST http://localhost:8080/process/<file_id>
Response:

json
Copy code
{"file_id":"<file_id>","word_count":<word_count>}
Retrieve the Processing Results
sh
Copy code
curl -X GET http://localhost:8080/result/<file_id>
Response:

json
Copy code
{
  "id":"<file_id>",
  "path":"<file_path>",
  "word_count":<word_count>
}
Example
Upload a PDF file:

sh
Copy code
curl -X POST -F "pdf=@C:/Users/admin/Desktop/Books2.pdf" http://localhost:8080/upload
Response:

json
Copy code
{"file_id":"66804449e867b"}
Process the uploaded PDF file:

sh
Copy code
curl -X POST http://localhost:8080/process/66804449e867b
Response:

json
Copy code
{"file_id":"66804449e867b","word_count":95}
Retrieve the processing results:

sh
Copy code
curl -X GET http://localhost:8080/result/66804449e867b
Response:

json
Copy code
{
  "id":"66804449e867b",
  "path":"C:\\xampp\\php\\pdf_service\\app\\/../uploads\\Books2.pdf",
  "word_count":95
}
License
This project is licensed under the MIT License - see the LICENSE file for details.

