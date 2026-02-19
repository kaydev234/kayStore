# KayStore Real-Time Payment API

This project now includes a minimal real-time payment backend using only Node.js built-in modules.

## Features
- Create payment intents
- Receive webhook-like status updates
- Stream payment status changes in real time using Server-Sent Events (SSE)
- Basic request validation and idempotent status updates

## Endpoints

### `POST /payments`
Create a payment intent.

Request body:
```json
{
  "amount": 49.99,
  "currency": "USD",
  "orderId": "order_123"
}
```

Response:
```json
{
  "paymentId": "pay_...",
  "status": "pending"
}
```

### `GET /payments/:id`
Get the latest payment details.

### `POST /webhooks/payment-status`
Update payment status (simulates PSP webhook).

Request body:
```json
{
  "paymentId": "pay_...",
  "status": "authorized"
}
```

Allowed statuses: `pending`, `authorized`, `captured`, `failed`, `refunded`.

### `GET /payments/:id/stream`
Open an SSE stream to receive real-time updates for a payment.

Sample event payload:
```json
{
  "type": "payment.updated",
  "payment": {
    "id": "pay_...",
    "status": "captured"
  }
}
```

## Run

```bash
node server.js
```

Server listens on `http://localhost:3000`.
