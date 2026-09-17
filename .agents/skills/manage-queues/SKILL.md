---
name: manage-queues
description: >-
  Use this skill when the user asks to manage, check, start, stop, or clear the job queues 
  for sending data to BPJS (Task IDs or Kode Booking).
---

# Manage Antrian BPJS Queues

The "Antrian BPJS Online" project relies heavily on Laravel Queues to send data to the BPJS API without timing out the web dashboard.
This skill instructs you on how to check the queue status and perform maintenance tasks.

## Queue Management Endpoints

The fastest and safest way to manage queues is via the web endpoints. Use `curl` or your HTTP client to hit these endpoints.
Assuming the application runs on `http://localhost/antrianbpjs/public` (adjust `APP_URL` if different):

### 1. Check Queue Status
Use this to see how many jobs are pending.
```bash
curl -X GET "http://localhost/antrianbpjs/public/api/queue-status"
```

### 2. Start the Queue Worker
Use this to run the worker in the background via the web endpoint.
```bash
curl -X GET "http://localhost/antrianbpjs/public/api/queue-work-start"
```

### 3. Stop the Queue Worker
Use this to gracefully stop running workers.
```bash
curl -X GET "http://localhost/antrianbpjs/public/api/queue-work-stop"
```

### 4. Clear All Queues
Use this if the queue is stuck or contains stale data that should not be sent.
```bash
curl -X GET "http://localhost/antrianbpjs/public/api/queue-clear"
```

## CLI Alternatives (Terminal)
If the web endpoints are not accessible, you can manage the queue via artisan commands from the project root (`c:\Users\tcomp\project\antrianbpjs`):

- **Start worker**: `php artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=90`
- **Restart worker**: `php artisan queue:restart`
- **Clear specific queue**: `php artisan queue:clear database --queue=default`
- **Clear failed jobs**: `php artisan queue:flush`
