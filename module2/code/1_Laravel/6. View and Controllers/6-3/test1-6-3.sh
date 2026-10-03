### Pages (HTML)

curl -i http://localhost:8080/                  # 302 redirect to /students
curl -s http://localhost:8080/students | head -n 20
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/students
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/students/1
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/students/create
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/students/1/edit

### Unknown Student (404)

curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/students/999999

### POST Without a CSRF Token (419 Page Expired)

curl -s -o /dev/null -w "%{http_code}\n" -X POST http://localhost:8080/students \
  -d 'name=Test&email=test@university.edu&age=20&major=Art&year=1'

### The JSON API Still Works

curl -H 'Accept: application/json' http://localhost:8080/api/students/1

### Routes

cd student-api && php artisan route:list --path=students
