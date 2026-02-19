const http = require('http');
const { randomUUID } = require('crypto');
const { URL } = require('url');

const PORT = process.env.PORT || 3000;
const ALLOWED_STATUSES = new Set(['pending', 'authorized', 'captured', 'failed', 'refunded']);

/** @type {Map<string, {id:string, amount:number, currency:string, orderId:string, status:string, updatedAt:string}>} */
const payments = new Map();
/** @type {Map<string, Set<import('http').ServerResponse>>} */
const subscribers = new Map();

function sendJson(res, statusCode, data) {
  const body = JSON.stringify(data);
  res.writeHead(statusCode, {
    'Content-Type': 'application/json',
    'Content-Length': Buffer.byteLength(body),
  });
  res.end(body);
}

function parseBody(req) {
  return new Promise((resolve, reject) => {
    let raw = '';
    req.on('data', (chunk) => {
      raw += chunk;
      if (raw.length > 1e6) {
        reject(new Error('Payload too large'));
        req.destroy();
      }
    });
    req.on('end', () => {
      if (!raw) return resolve({});
      try {
        resolve(JSON.parse(raw));
      } catch {
        reject(new Error('Invalid JSON'));
      }
    });
    req.on('error', reject);
  });
}

function publishPaymentUpdate(payment) {
  const clientSet = subscribers.get(payment.id);
  if (!clientSet || clientSet.size === 0) return;

  const data = JSON.stringify({
    type: 'payment.updated',
    payment: {
      id: payment.id,
      status: payment.status,
      updatedAt: payment.updatedAt,
    },
  });

  for (const res of clientSet) {
    res.write(`event: payment.updated\n`);
    res.write(`data: ${data}\n\n`);
  }
}

function createPayment(body) {
  if (typeof body.amount !== 'number' || body.amount <= 0) {
    throw new Error('amount must be a positive number');
  }
  if (typeof body.currency !== 'string' || body.currency.length !== 3) {
    throw new Error('currency must be a 3-letter code');
  }
  if (typeof body.orderId !== 'string' || body.orderId.trim() === '') {
    throw new Error('orderId is required');
  }

  const payment = {
    id: `pay_${randomUUID()}`,
    amount: body.amount,
    currency: body.currency.toUpperCase(),
    orderId: body.orderId,
    status: 'pending',
    updatedAt: new Date().toISOString(),
  };

  payments.set(payment.id, payment);
  return payment;
}

function updatePaymentStatus(body) {
  const { paymentId, status } = body;
  if (typeof paymentId !== 'string' || paymentId.trim() === '') {
    throw new Error('paymentId is required');
  }
  if (!ALLOWED_STATUSES.has(status)) {
    throw new Error(`status must be one of: ${Array.from(ALLOWED_STATUSES).join(', ')}`);
  }

  const payment = payments.get(paymentId);
  if (!payment) {
    const notFoundError = new Error('payment not found');
    notFoundError.code = 404;
    throw notFoundError;
  }

  if (payment.status === status) {
    return payment;
  }

  payment.status = status;
  payment.updatedAt = new Date().toISOString();
  publishPaymentUpdate(payment);
  return payment;
}

function handleSse(req, res, paymentId) {
  const payment = payments.get(paymentId);
  if (!payment) {
    return sendJson(res, 404, { error: 'payment not found' });
  }

  res.writeHead(200, {
    'Content-Type': 'text/event-stream',
    'Cache-Control': 'no-cache',
    Connection: 'keep-alive',
  });

  if (!subscribers.has(paymentId)) {
    subscribers.set(paymentId, new Set());
  }
  subscribers.get(paymentId).add(res);

  res.write(`event: payment.snapshot\n`);
  res.write(`data: ${JSON.stringify({ type: 'payment.snapshot', payment })}\n\n`);

  const heartbeat = setInterval(() => {
    res.write(': ping\n\n');
  }, 15000);

  req.on('close', () => {
    clearInterval(heartbeat);
    const clientSet = subscribers.get(paymentId);
    if (!clientSet) return;
    clientSet.delete(res);
    if (clientSet.size === 0) subscribers.delete(paymentId);
  });
}

const server = http.createServer(async (req, res) => {
  const url = new URL(req.url, `http://${req.headers.host}`);

  try {
    if (req.method === 'POST' && url.pathname === '/payments') {
      const body = await parseBody(req);
      const payment = createPayment(body);
      return sendJson(res, 201, { paymentId: payment.id, status: payment.status });
    }

    const paymentMatch = url.pathname.match(/^\/payments\/(pay_[^/]+)$/);
    if (req.method === 'GET' && paymentMatch) {
      const payment = payments.get(paymentMatch[1]);
      if (!payment) return sendJson(res, 404, { error: 'payment not found' });
      return sendJson(res, 200, payment);
    }

    const streamMatch = url.pathname.match(/^\/payments\/(pay_[^/]+)\/stream$/);
    if (req.method === 'GET' && streamMatch) {
      return handleSse(req, res, streamMatch[1]);
    }

    if (req.method === 'POST' && url.pathname === '/webhooks/payment-status') {
      const body = await parseBody(req);
      const updated = updatePaymentStatus(body);
      return sendJson(res, 200, { ok: true, paymentId: updated.id, status: updated.status });
    }

    if (req.method === 'GET' && url.pathname === '/healthz') {
      return sendJson(res, 200, { ok: true });
    }

    return sendJson(res, 404, { error: 'not found' });
  } catch (error) {
    const status = error.code || 400;
    return sendJson(res, status, { error: error.message || 'request failed' });
  }
});

server.listen(PORT, () => {
  console.log(`Real-time payment server listening on http://localhost:${PORT}`);
});
