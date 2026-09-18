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

## Task ID Management Endpoints (New Features)
The system now includes specific endpoints to manage and resync Task IDs manually without waiting for the automatic cron.

### 1. Manual Add Task ID
Allows inserting a duplicate or completely manual Task ID row into the queue.
- **Endpoint:** `POST /api/manual-add-taskid`
- **Parameters:** `urlQL` (Branch code), `kodebooking`, `taskid`, `waktu` (timestamp in ms), `idpendaftaran`.
- **Behavior:** Inserts the row with `reupload = 1` and `code = 0`.

### 2. Manual Edit Task ID
Allows correcting existing Task ID rows (e.g. wrong time or ID pendaftaran).
- **Endpoint:** `POST /api/manual-edit-taskid`
- **Parameters:** `id` (Primary Key), `urlQL`, `kodebooking`, `taskid`, `waktu` (timestamp in ms), `idpendaftaran`.
- **Behavior:** Updates the row and resets its status to `reupload = 1` and `code = 0` (Menunggu Sinkronisasi).

### 3. Sync Task ID by Kodebooking
Allows forcefully resending all Task IDs associated with a specific Kode Booking.
- **Endpoint:** `GET /api/run-taskid-by-kodebooking?urlQL={BRANCH}&kodebooking={KB}`
- **Behavior:** Sets `reupload = 1` for all rows matching the `kodebooking`, then instantly processes them sequentially to BPJS. This endpoint is extremely useful to clear out individual stuck booking flows.
