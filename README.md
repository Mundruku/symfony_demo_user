# Running the Project with Docker

Follow the steps below to build and run the Symfony project using Docker Compose.

---

## 🚀 Setup Instructions

### 1. Build fresh Docker images

Build the images from scratch and pull the latest base images:

```bash
docker compose build --pull --no-cache
```

---

### 2. Start the Symfony application

This will set up and start a  Symfony DEMO TASK project and wait until all services are ready.:

```bash
docker compose up --wait
```

---

### 3. Access the application

Open the application in your web browser:

```
 http://localhost:8000
```

---

### 4. Stop the Docker containers

When you are done, stop all containers and remove orphaned services:

```bash
docker compose down --remove-orphans
```

---
