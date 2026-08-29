# Week 4: Request and Response with PHP

These examples correspond to `src/3_Request_and_Response_with_PHP`.

1. Start the PHP development server in this directory:

   ```bash
   php -S localhost:8000
   ```

2. Inspect a GET request:

   ```bash
   curl "http://localhost:8000/request_info.php?name=Alice"
   ```

3. Send JSON with POST or PUT:

   ```bash
   curl -X POST http://localhost:8000/methods.php \
     -H "Content-Type: application/json" \
     -d '{"name":"Alice"}'

   curl -X PUT http://localhost:8000/methods.php \
     -H "Content-Type: application/json" \
     -d '{"name":"Alice Updated"}'
   ```

The examples intentionally focus on method, path, query parameters, headers,
body, status code, and JSON response—the core concepts in the Week 4 material.
