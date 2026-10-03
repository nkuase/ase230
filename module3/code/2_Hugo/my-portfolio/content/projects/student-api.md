---
title: "Student Management API"
date: 2026-01-15
description: "REST API for managing student records, built with Laravel and MySQL"
draft: false
---

A REST API for managing student records. It runs locally or in Docker.

![Student API screenshot](/images/student-api.png)

![Docker dashboard](/images/docker-dashboard.png)

## Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/students` | List all students |
| GET | `/api/students/{id}` | Get one student |
| POST | `/api/students` | Create a student |
| PUT | `/api/students/{id}` | Update a student |
| DELETE | `/api/students/{id}` | Delete a student |

## Example

Create a student:

```bash
curl -X POST http://localhost:8000/api/students \
  -H "Content-Type: application/json" \
  -d '{"name": "Alice Johnson", "email": "alice@university.edu"}'
```

Response:

```json
{
  "success": true,
  "data": { "id": 1, "name": "Alice Johnson", "email": "alice@university.edu" }
}
```

## Run It

```bash
git clone https://github.com/yourusername/student-api.git
cd student-api
docker compose up -d
```

The API is now available at `http://localhost:8000`.
