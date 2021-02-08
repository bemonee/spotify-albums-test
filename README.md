# Spotify integration test

Used technologies: `PHP 7, Slim 4, dotenv, Docker & Docker Compose, Guzzle`.

### Requirements:

- Composer.
- PHP 7.4+.
- Docker

**Install project:**

```bash
# Install dependencies
composer install
```

**Start project:**

```bash
# Create and start containers for the API.
docker-compose up -d --build
```

**Configure env:**

```bash
# Copy enviroment variables
cp .env.example .env
```

**Query your favourite band:**
```
http://localhost:8081/api/v1/albums?q=<band-name>
```
