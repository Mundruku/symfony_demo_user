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

This will set up and start a fresh Symfony project and wait until all services are ready:

```bash
docker compose up --wait
```

---

### 3. Access the application

Open the application in your web browser:

```
https://localhost
```

⚠️ You may see a browser warning due to the auto-generated TLS certificate.  
This is expected for local development—please accept the certificate to continue.

---

### 4. Stop the Docker containers

When you are done, stop all containers and remove orphaned services:

```bash
docker compose down --remove-orphans
```

---
