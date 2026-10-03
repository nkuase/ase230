# Docker version of Module 2's test1-5.sh (port 8000; php and mysql run in containers).
# Run it from this folder after "bash run1-5-docker.sh":  bash test1-5-docker.sh
cd "$(dirname "${BASH_SOURCE[0]}")"

### Public Route (no token needed)

curl -H 'Accept: application/json' http://localhost:8000/api/students/1

### Login: Get a Bearer Token

curl -i -X POST http://localhost:8000/api/login \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"email":"student@university.edu","password":"student123"}'

# Wrong password -> 401
curl -i -X POST http://localhost:8000/api/login \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"email":"student@university.edu","password":"wrong"}'

### Save the Token in a Variable

TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"email":"student@university.edu","password":"student123"}' \
  | docker compose exec -T php php -r 'echo json_decode(stream_get_contents(STDIN))->token;')
echo "$TOKEN"

### Protected Route: Missing, Invalid, and Valid Token

curl -i -H 'Accept: application/json' http://localhost:8000/api/me
curl -i -H 'Accept: application/json' -H 'Authorization: Bearer invalid-token' http://localhost:8000/api/me
curl -i -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/me

### Protected Write: Create a Student

curl -i -X POST http://localhost:8000/api/students \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"name":"Grace Lee","email":"grace.lee@university.edu","age":20,"major":"Computer Science","year":2}'
curl -i -X POST http://localhost:8000/api/students \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"Grace Lee","email":"grace.lee@university.edu","age":20,"major":"Computer Science","year":2}'

### Look Inside the Token Table

docker compose exec -T mysql mysql -u ase230 -pase230pass studentdb -e "SELECT id, tokenable_id, name, LEFT(token, 12) AS token_hash, expires_at FROM personal_access_tokens"

### Logout: The Token Stops Working

curl -X POST http://localhost:8000/api/logout -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN"
curl -i -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/me
