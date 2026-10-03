### Read Records

curl http://localhost:8080/api/students
curl http://localhost:8080/api/students/1
curl -i -H 'Accept: application/json' http://localhost:8080/api/students/999

### Save a Record

curl -i -X POST http://localhost:8080/api/students \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"name":"Grace Lee","email":"grace.lee@university.edu","age":20,"major":"Computer Science","year":2}'

### Verify Persistence

mysql -u ase230 -p studentdb -e "SELECT id, name, age, major, year FROM students"

### Update and Delete

curl -X PUT http://localhost:8080/api/students/9 \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"major":"Mathematics"}'
curl -X DELETE http://localhost:8080/api/students/9 -H 'Accept: application/json'
curl -i -H 'Accept: application/json' http://localhost:8080/api/students/9