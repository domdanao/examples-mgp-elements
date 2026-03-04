# Magpie Components — Developer Documentation

## Table of Contents

1. [Overview](#1-overview)
2. [How It Works](#2-how-it-works)
3. [Prerequisites](#3-prerequisites)
4. [Quick Start](#4-quick-start)
5. [SDK Reference](#5-sdk-reference)
   - [Magpie](#magpie)
   - [Elements](#elements)
   - [Element](#element)
6. [Element Types](#6-element-types)
7. [Styling](#7-styling)
8. [createSource()](#8-createsource)
9. [Event Handling](#9-event-handling)
10. [postMessage Protocol](#10-postmessage-protocol)
11. [API Reference](#11-api-reference)
12. [Origin Allowlists](#12-origin-allowlists)
13. [Local Development](#13-local-development)
14. [Production Setup](#14-production-setup)
15. [Security Model](#15-security-model)
16. [Error Handling & Troubleshooting](#16-error-handling--troubleshooting)
17. [Full Working Example](#17-full-working-example)

---

## 1. Overview

**Magpie Components** is a secure, iframe-based card input system for collecting payment card data without exposing sensitive information to your application. It is made up of three parts:

- **SDK** (`magpie.js`) — A JavaScript library you load on your page. It creates and manages iframes and exposes a simple API for your code to call.
- **Components** — Lightweight HTML pages served from `https://components.magpie.im` that run inside the iframes. They render the actual card input fields and handle all card data directly.
- **Proxy API** — A Laravel backend at `https://components.magpie.im/api/v2` that receives card data from the components and forwards it securely to the Magpie API using the merchant's own public key.

Card data (number, expiry, CVC) is entered directly inside the iframes. It never touches your JavaScript, your DOM, or your server. Only a **source token** (`src_xxx`) is returned to your page.

---

## 2. How It Works

```
Your Page
  └─ Loads magpie.js
       └─ Creates three iframes (cardNumber, cardExpiry, cardCvc)
            └─ Each iframe loads from https://components.magpie.im
                 └─ User types card data inside iframes
                      └─ On createSource():
                           └─ iframe POSTs card data to /api/v2/sources
                                └─ Proxy forwards to api.magpie.im with merchant key
                                     └─ Returns source token (src_xxx) to your page
```

**Why iframes?**
The browser's same-origin policy means your JavaScript cannot read the contents of a cross-origin iframe. Card data typed into the iframes is invisible to your page — only the non-sensitive source token is returned.

**Key Design Decisions**
- Each card field is a separate iframe (number, expiry, CVC).
- The three iframes synchronize their state using the browser's `BroadcastChannel` API.
- Communication between your page and the iframes uses `window.postMessage` with strict origin validation on both sides.
- The merchant's API key is sent from your page to the iframe via `postMessage` during initialization. It is never sent to your server.

---

## 3. Prerequisites

- A Magpie **public API key** (`pk_live_...` or `pk_test_...`).
- Your site must be served over **HTTPS** in production.
- Your origin must be on the Magpie Components allowlist (see [Section 12](#12-origin-allowlists)).
- Modern browser support: Chrome, Firefox, Safari, Edge (BroadcastChannel API required).

---

## 4. Quick Start

**Step 1 — Add the SDK script to your page.**

```html
<script src="https://components.magpie.im/sdk/magpie.js"></script>
```

**Step 2 — Add container elements for each card field.**

```html
<div id="card-number"></div>
<div id="card-expiry"></div>
<div id="card-cvc"></div>
```

**Step 3 — Initialize the SDK and mount the elements.**

```javascript
const magpie = new Magpie("pk_live_your_key");
const elements = magpie.elements();

const cardNumber = elements.create("cardNumber");
const cardExpiry = elements.create("cardExpiry");
const cardCvc    = elements.create("cardCvc");

cardNumber.mount("#card-number");
cardExpiry.mount("#card-expiry");
cardCvc.mount("#card-cvc");
```

**Step 4 — Call `createSource()` when the user submits.**

```javascript
document.querySelector("#pay").addEventListener("click", async () => {
  try {
    const source = await cardNumber.createSource({
      name: "Cardholder Name",  // Required
      redirect: {
        success: "https://your-site.com/checkout/success",
        fail: "https://your-site.com/checkout/fail",
        notify: "https://your-site.com/checkout/notify"
      }
    });

    // source.id is the token — send it to your server
    console.log("Source created:", source.id);
  } catch (err) {
    console.error("Error:", err.message);
  }
});
```

That's it. Your page receives a `source.id` which you send to your server to complete the charge.

---

## 5. SDK Reference

### `Magpie`

The root class. Instantiate once per page.

```javascript
const magpie = new Magpie(publicKey, options);
```

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `publicKey` | `string` | Yes | Your Magpie public API key (`pk_live_...` or `pk_test_...`) |
| `options.componentsUrl` | `string` | No | Override the components base URL. Defaults to `https://components.magpie.im` |

**Example:**

```javascript
// Production
const magpie = new Magpie("pk_live_abc123");

// Custom components URL (e.g. local development)
const magpie = new Magpie("pk_test_abc123", {
  componentsUrl: "https://elements-dev.your-domain.com"
});
```

---

### `Elements`

A factory for creating individual card field elements. Obtain it by calling `magpie.elements()`.

```javascript
const elements = magpie.elements();
```

#### `elements.create(type, options)`

Creates a single card field element.

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `type` | `string` | Yes | One of: `"cardNumber"`, `"cardExpiry"`, `"cardCvc"` |
| `options.style` | `object` | No | CSS style overrides applied inside the iframe (see [Section 7](#7-styling)) |

Returns an `Element` instance.

---

### `Element`

Represents a single card input field. The main methods you will use are `mount()` and `createSource()`.

#### `element.mount(selector)`

Mounts the iframe into a DOM element. Call once per element after the DOM is ready.

```javascript
cardNumber.mount("#card-number");
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `selector` | `string` | A CSS selector for the container element |

The iframe is sized to fill the container. Style the container yourself (border, padding, border-radius, etc.).

#### `element.createSource(data)`

Triggers card data collection from all three fields and submits to the Magpie API. Returns a `Promise` that resolves with the source object or rejects with an error.

```javascript
const source = await cardNumber.createSource(data);
```

See [Section 8](#8-createsource) for full parameter and response documentation.

> **Note:** Call `createSource()` on any one of the three element instances — it does not matter which. All three iframes share state via `BroadcastChannel`.

---

## 6. Element Types

| Type | Field | Validation | Auto-format |
|------|-------|------------|-------------|
| `cardNumber` | Card number | Luhn algorithm, min 16 digits | Groups of 4 digits with spaces |
| `cardExpiry` | Expiry date | Valid month (1–12), future date | `MM / YY` |
| `cardCvc` | CVC / CVV | 3–4 digits | Digits only |

**Auto-focus behavior:** When the card number field is complete, focus moves automatically to the expiry field. When expiry is complete, focus moves to the CVC field. This is handled by the SDK automatically — you do not need to wire it up.

**Brand detection:** The card number field detects and displays the card brand icon as the user types. Supported brands: Visa, Mastercard, JCB, Amex, Discover.

---

## 7. Styling

Pass a `style` object when calling `elements.create()`. Styles are applied inside the iframe to the `<input>` element.

```javascript
const cardNumber = elements.create("cardNumber", {
  style: {
    base: {
      fontFamily: "Inter, system-ui, sans-serif",
      fontSize: "16px",
      fontWeight: "400",
      color: "#111827",
      "::placeholder": {
        color: "#9CA3AF"
      }
    }
  }
});
```

**Supported style properties:**

| Property | Example |
|----------|---------|
| `fontFamily` | `"Inter, sans-serif"` |
| `fontSize` | `"16px"` |
| `fontWeight` | `"400"` |
| `color` | `"#111827"` |
| `::placeholder` | `{ color: "#9CA3AF" }` |

**Container styling** (the element that holds the iframe) is done with regular CSS on your page:

```css
.field {
  border: 1px solid #D1D5DB;
  border-radius: 6px;
  padding: 10px 12px;
  height: 44px;
  box-sizing: border-box;
}
```

---

## 8. `createSource()`

The card number, expiry, and CVC are collected automatically from the mounted fields. Everything else — cardholder details, billing/shipping info, and redirect URLs — is passed as an argument to `createSource()`.

### Minimal call

The card fields (number, expiry, CVC), cardholder name, and redirect URLs are required:

```javascript
const source = await cardNumber.createSource({
  name: "Cardholder Name",
  redirect: {
    success: "https://your-site.com/success",
    fail: "https://your-site.com/fail",
    notify: "https://your-site.com/notify"
  }
});
```

### Complete call

```javascript
const source = await cardNumber.createSource({
  name: "Gerry Isaac",
  address_line1: "#123 JP Rizal St.",
  address_line2: "Brgy. Aguinaldo",
  address_city: "Quezon City",
  address_state: "Metro Manila",
  address_country: "PH",
  address_zip: "1100",
  redirect: {
    success: "https://your-site.com/checkout/success",
    fail: "https://your-site.com/checkout/fail",
    notify: "https://your-site.com/checkout/notify"
  },
  owner: {
    name: "Gerry Isaac",
    address_country: "PH",
    billing: {
      name: "Gerry Isaac",
      phone_number: "639175511222",
      email: "client@example.com",
      line1: "#123 JP Rizal St.",
      line2: "Brgy. Aguinaldo",
      city: "Quezon City",
      state: "Metro Manila",
      country: "PH",
      zip_code: "1100"
    },
    shipping: {
      name: "Gerry Isaac",
      phone_number: "639175511222",
      email: "client@example.com",
      line1: "#123 JP Rizal St.",
      line2: "Brgy. Aguinaldo",
      city: "Quezon City",
      state: "Metro Manila",
      country: "PH",
      zip_code: "1100"
    }
  },
  metadata: {
    order_id: "ord_123",
    customer_id: "cust_456"
  }
});
```

### Parameters

The card number, expiry, and CVC come from the mounted input fields. The `name` and `redirect` fields are required. All other fields are optional.

**Card fields**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | `string` | **Yes** | Cardholder name as it appears on the card |
| `redirect.success` | `string` | **Yes** | URL to redirect to after successful 3DS authentication |
| `redirect.fail` | `string` | **Yes** | URL to redirect to after failed 3DS authentication |
| `redirect.notify` | `string` | **Yes** | Webhook URL for async payment status notifications |
| `address_line1` | `string` | No | Card billing address line 1 |
| `address_line2` | `string` | No | Card billing address line 2 |
| `address_city` | `string` | No | Card billing city |
| `address_state` | `string` | No | Card billing state or province |
| `address_country` | `string` | No | Card billing country (ISO 3166-1 alpha-2, e.g. `"PH"`) |
| `address_zip` | `string` | No | Card billing postal code |



**Owner**

The `owner` object carries full billing and shipping contact details. These are separate from the card address fields above and are passed directly to the Magpie API.

| Field | Type | Description |
|-------|------|-------------|
| `owner.name` | `string` | Owner's full name |
| `owner.address_country` | `string` | Owner's country (ISO 3166-1 alpha-2) |
| `owner.billing.name` | `string` | Billing contact full name |
| `owner.billing.phone_number` | `string` | Billing contact phone number (e.g. `"639175511222"`) |
| `owner.billing.email` | `string` | Billing contact email address |
| `owner.billing.line1` | `string` | Billing address line 1 |
| `owner.billing.line2` | `string` | Billing address line 2 |
| `owner.billing.city` | `string` | Billing city |
| `owner.billing.state` | `string` | Billing state or province |
| `owner.billing.country` | `string` | Billing country (ISO 3166-1 alpha-2) |
| `owner.billing.zip_code` | `string` | Billing postal code |
| `owner.shipping.name` | `string` | Shipping contact full name |
| `owner.shipping.phone_number` | `string` | Shipping contact phone number |
| `owner.shipping.email` | `string` | Shipping contact email address |
| `owner.shipping.line1` | `string` | Shipping address line 1 |
| `owner.shipping.line2` | `string` | Shipping address line 2 |
| `owner.shipping.city` | `string` | Shipping city |
| `owner.shipping.state` | `string` | Shipping state or province |
| `owner.shipping.country` | `string` | Shipping country (ISO 3166-1 alpha-2) |
| `owner.shipping.zip_code` | `string` | Shipping postal code |

**Metadata**

| Field | Type | Description |
|-------|------|-------------|
| `metadata` | `object` or `array` | Any custom key-value data you want to attach to the source. Returned as-is in the response. |

### Response

On success, the promise resolves with a source object:

```json
{
  "object": "source",
  "id": "src_019cb34573611648c15c18cd55bd9a4b",
  "type": "card",
  "card": {
    "object": "card",
    "id": "card_019cb3457361c1e3716ac60d57556ab0",
    "name": "Gerry Isaac",
    "last4": "1112",
    "exp_month": "12",
    "exp_year": "2026",
    "address_line1": "#123 JP Rizal St.",
    "address_line2": "Brgy. Aguinaldo",
    "address_city": "Quezon City",
    "address_state": "Metro Manila",
    "address_country": "PH",
    "address_zip": "1100",
    "brand": "visa",
    "country": "PH",
    "cvc_checked": "",
    "funding": "debit",
    "issuing_bank": "VISA PRODUCTION SUPPORT CLIENT BID 1"
  },
  "bank_account": null,
  "redirect": {
    "success": "https://your-site.com/checkout/success",
    "fail": "https://your-site.com/checkout/fail",
    "notify": "https://your-site.com/checkout/notify"
  },
  "owner": {
    "billing": {
      "name": "Gerry Isaac",
      "phone_number": "639175511222",
      "email": "client@example.com",
      "line1": "#123 JP Rizal St.",
      "line2": "Brgy. Aguinaldo",
      "city": "Quezon City",
      "state": "Metro Manila",
      "country": "PH",
      "zip_code": "1100"
    },
    "shipping": {
      "name": "Gerry Isaac",
      "phone_number": "639175511222",
      "email": "client@example.com",
      "line1": "#123 JP Rizal St.",
      "line2": "Brgy. Aguinaldo",
      "city": "Quezon City",
      "state": "Metro Manila",
      "country": "PH",
      "zip_code": "1100"
    },
    "name": "Gerry Isaac",
    "address_country": "PH"
  },
  "vaulted": false,
  "used": false,
  "livemode": true,
  "created_at": "2026-03-03T18:36:39.304616+08:00",
  "updated_at": "2026-03-03T18:36:39.462930+08:00",
  "metadata": {}
}
```

### Errors

On failure, the promise rejects with an error object. Always wrap `createSource()` in a try/catch:

```javascript
try {
  const source = await cardNumber.createSource({ ... });
} catch (err) {
  // err.message contains the error description
  console.error(err.message);
}
```

Common error messages:

| Message | Cause |
|---------|-------|
| `"Card number is required"` | Card number field is empty |
| `"Card expiry is required"` | Expiry field is empty |
| `"CVC is required"` | CVC field is empty |
| `"Invalid expiry date format"` | Expiry could not be parsed |
| `"The API key doesn't have permissions to perform the request."` | Wrong key type or invalid key |
| `"Validation failed: ..."` | Card data failed server-side validation |

---

## 9. Event Handling

The SDK fires events as the user interacts with card fields. Listen for them on an element instance.

> **Note:** Event listening is built into the SDK's internal `postMessage` handler. The events listed below are the `action` values received from the iframes.

### `FIELD_COMPLETE`

Fired when a field has a valid, complete value.

**Triggered by:**
- `cardNumber`: Luhn-valid number of at least 16 digits
- `cardExpiry`: Valid `MM / YY` with a future date
- `cardCvc`: 3–4 digits entered

The SDK uses this internally to auto-advance focus. You can also use it to drive UI feedback (e.g. green border on complete fields) by listening to `postMessage` events from the iframe:

```javascript
window.addEventListener("message", (event) => {
  if (event.origin !== "https://components.magpie.im") return;

  if (event.data?.action === "FIELD_COMPLETE") {
    const field = event.data.field; // "cardNumber" | "cardExpiry" | "cardCvc"
    document.querySelector(`#${field}-container`).classList.add("complete");
  }

  if (event.data?.action === "FIELD_EMPTY") {
    const field = event.data.field;
    document.querySelector(`#${field}-container`).classList.remove("complete");
  }
});
```

### `FIELD_EMPTY`

Fired when a previously complete field is cleared (e.g. the user backspaces to empty).

### `READY`

Fired by each iframe once it has loaded and validated the `INIT` message from the SDK. The SDK handles this internally — you do not need to wait for it manually.

### `SOURCE_CREATED`

Fired by the iframe when the Magpie API returns a successful source. Handled internally by the SDK — your `createSource()` promise resolves with the payload.

### `SOURCE_ERROR`

Fired by the iframe when source creation fails. Handled internally by the SDK — your `createSource()` promise rejects with the error.

---

## 10. postMessage Protocol

This section is for advanced use cases where you are building a custom integration or debugging the SDK internals.

### Messages sent by the SDK to each iframe

| Action | Payload | Description |
|--------|---------|-------------|
| `INIT` | `{ apiKey, parentOrigin }` | Sent once after the iframe loads. Passes the merchant's public key and the current page origin. |
| `CREATE_SOURCE` | `{ additionalData }` | Triggers source creation. `additionalData` contains cardholder name, address, redirect URL. |
| `UPDATE_STYLE` | `{ fontFamily, fontSize, color, ... }` | Applies style overrides to the input field inside the iframe. |
| `CMD_FOCUS` | — | Requests the iframe to focus its input. Used for auto-advance between fields. |

### Messages sent by iframes to the SDK

| Action | Payload | Description |
|--------|---------|-------------|
| `READY` | — | Iframe has initialized and is ready. |
| `FIELD_COMPLETE` | `{ field: "cardNumber" \| "cardExpiry" \| "cardCvc" }` | Field value is valid and complete. |
| `FIELD_EMPTY` | `{ field: "cardNumber" \| "cardExpiry" \| "cardCvc" }` | Field was cleared. |
| `SOURCE_CREATED` | Source object | Source was created successfully. |
| `SOURCE_ERROR` | `{ error, debug }` | Source creation failed. |

### Origin validation

- The SDK sends all `postMessage` calls with `targetOrigin` set to `componentsUrl` (e.g. `https://components.magpie.im`). Messages are rejected by the browser if the iframe is not at that exact origin.
- Each iframe validates that `event.origin` on received messages matches the `parentOrigin` sent in the `INIT` message.
- The SDK validates that `event.origin` on received messages matches `componentsUrl`.

---

## 11. API Reference

### `POST /api/v2/sources`

Creates a payment source. This endpoint is called internally by the iframe — **you should not call it directly from your page**.

**Authentication:** `Authorization: Basic <base64(pk_live_key:)>`

**Minimal request body** (required fields):

```json
{
  "type": "card",
  "card": {
    "name": "Cardholder Name",
    "number": "4012001037141112",
    "exp_month": "12",
    "exp_year": "2025",
    "cvc": "123"
  },
  "redirect": {
    "success": "https://your-site.com/success",
    "fail": "https://your-site.com/fail",
    "notify": "https://your-site.com/notify"
  }
}
```

**Complete request body** (all fields included):

```json
{
  "type": "card",
  "card": {
    "name": "Gerry Isaac",
    "number": "4012001037141112",
    "exp_month": "12",
    "exp_year": "2025",
    "cvc": "123",
    "address_line1": "#123 JP Rizal St.",
    "address_line2": "Brgy. Aguinaldo",
    "address_city": "Quezon City",
    "address_state": "Metro Manila",
    "address_country": "PH",
    "address_zip": "1100"
  },
  "redirect": {
    "success": "https://your-site.com/checkout/success",
    "fail": "https://your-site.com/checkout/fail",
    "notify": "https://your-site.com/checkout/notify"
  },
  "owner": {
    "name": "Gerry Isaac",
    "address_country": "PH",
    "billing": {
      "name": "Gerry Isaac",
      "phone_number": "639175511222",
      "email": "client@example.com",
      "line1": "#123 JP Rizal St.",
      "line2": "Brgy. Aguinaldo",
      "city": "Quezon City",
      "state": "Metro Manila",
      "country": "PH",
      "zip_code": "1100"
    },
    "shipping": {
      "name": "Gerry Isaac",
      "phone_number": "639175511222",
      "email": "client@example.com",
      "line1": "#123 JP Rizal St.",
      "line2": "Brgy. Aguinaldo",
      "city": "Quezon City",
      "state": "Metro Manila",
      "country": "PH",
      "zip_code": "1100"
    }
  },
  "metadata": {
    "order_id": "ord_123",
    "customer_id": "cust_456"
  }
}
```

**Response (200):**

```json
{
  "object": "source",
  "id": "src_019cb34573611648c15c18cd55bd9a4b",
  "type": "card",
  "card": {
    "object": "card",
    "id": "card_019cb3457361c1e3716ac60d57556ab0",
    "name": "Gerry Isaac",
    "last4": "1112",
    "exp_month": "12",
    "exp_year": "2026",
    "address_line1": "#123 JP Rizal St.",
    "address_line2": "Brgy. Aguinaldo",
    "address_city": "Quezon City",
    "address_state": "Metro Manila",
    "address_country": "PH",
    "address_zip": "1100",
    "brand": "visa",
    "country": "PH",
    "cvc_checked": "",
    "funding": "debit",
    "issuing_bank": "VISA PRODUCTION SUPPORT CLIENT BID 1"
  },
  "bank_account": null,
  "redirect": {
    "success": "https://your-site.com/checkout/success",
    "fail": "https://your-site.com/checkout/fail",
    "notify": "https://your-site.com/checkout/notify"
  },
  "owner": {
    "billing": {
      "name": "Gerry Isaac",
      "phone_number": "639175511222",
      "email": "client@example.com",
      "line1": "#123 JP Rizal St.",
      "line2": "Brgy. Aguinaldo",
      "city": "Quezon City",
      "state": "Metro Manila",
      "country": "PH",
      "zip_code": "1100"
    },
    "shipping": {
      "name": "Gerry Isaac",
      "phone_number": "639175511222",
      "email": "client@example.com",
      "line1": "#123 JP Rizal St.",
      "line2": "Brgy. Aguinaldo",
      "city": "Quezon City",
      "state": "Metro Manila",
      "country": "PH",
      "zip_code": "1100"
    },
    "name": "Gerry Isaac",
    "address_country": "PH"
  },
  "vaulted": false,
  "used": false,
  "livemode": true,
  "created_at": "2026-03-03T18:36:39.304616+08:00",
  "updated_at": "2026-03-03T18:36:39.462930+08:00",
  "metadata": {}
}
```

**Middleware applied:**
- `origin.verify` — validates the request's `Origin` header against the allowlist
- `throttle:300,1` — 300 requests per minute per IP

---

### `POST /api/v2/charges`

Creates a charge against an existing source. Call this from your **server** using your **secret key**. Never call it from the browser.

**Authentication:** `Authorization: Basic <base64(sk_live_key:)>`

**Request body:**

```json
{
  "amount": 10000,
  "currency": "php",
  "source": "src_abc123",
  "description": "Order #456",
  "statement_descriptor": "My Store"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `amount` | `integer` | Yes | Amount in the smallest currency unit (e.g. centavos for PHP) |
| `currency` | `string` | Yes | `"php"` or `"usd"` |
| `source` | `string` | Yes | Source ID returned from `createSource()` |
| `description` | `string` | Yes | Description of the charge |
| `statement_descriptor` | `string` | No | Appears on card statement. Max 22 characters. |

**Response (200):**

```json
{
  "object": "charge",
  "id": "ch_xyz789",
  "amount": 10000,
  "amount_refunded": 0,
  "captured": true,
  "currency": "php",
  "description": "Order #456",
  "statement_descriptor": "My Store",
  "status": "succeeded",
  "source": {
    "id": "src_abc123",
    "type": "card",
    "card": { "last4": "4242", "brand": "visa", ... }
  },
  "created_at": "2026-03-03T10:00:00Z"
}
```

---

### `GET /api/v2/health`

Returns the service status. Use for uptime monitoring.

**Response (200):**

```json
{ "status": "ok" }
```

---

## 12. Origin Allowlists

Magpie Components enforces two separate allowlists:

| Allowlist | Controls | Environment variable |
|-----------|----------|---------------------|
| Embed allowlist | Which origins may embed the iframe | `MAGPIE_EMBED_ALLOWED_ORIGINS` |
| API allowlist | Which origins may call `/api/v2/sources` | `MAGPIE_API_ALLOWED_ORIGINS` |

Both must include your origin for the integration to work end-to-end.

### Environment-based allowlists (default)

Set comma-separated origins in your `.env`:

```env
MAGPIE_EMBED_ALLOWED_ORIGINS=https://checkout.your-site.com,https://pay.your-site.com
MAGPIE_API_ALLOWED_ORIGINS=https://components.magpie.im
```

For regex-based matching (e.g. to cover all subdomains):

```env
MAGPIE_API_ALLOWED_ORIGIN_PATTERNS=/^https:\/\/.*\.your-site\.com$/
```

### Database-based allowlists (per API key)

For fine-grained control where each merchant key has its own allowed origin:

```env
MAGPIE_USE_DB_ALLOWED_ORIGINS=true
```

Run the migration:

```bash
php artisan migrate --force
```

Add an allowed origin for a specific key:

```bash
php artisan magpie:allow-origin pk_live_merchant_key https://checkout.merchant-site.com
```

When `MAGPIE_USE_DB_ALLOWED_ORIGINS=true`, the middleware extracts the API key from the `Authorization` header of each request and checks the `allowed_origins` table for a matching `(api_key, origin)` row.

---

## 13. Local Development

### Step 1 — Set environment variables

In your local `.env`:

```env
APP_URL=https://elements-dev.your-domain.test
MAGPIE_API_URL=https://api.magpie.im/v2

MAGPIE_EMBED_ALLOWED_ORIGINS=https://your-demo-page.test
MAGPIE_API_ALLOWED_ORIGINS=https://elements-dev.your-domain.test

MAGPIE_USE_DB_ALLOWED_ORIGINS=false
MAGPIE_DEBUG_ROUTES=false
```

### Step 2 — Point the SDK at your local components

In your demo page, pass `componentsUrl` when initializing:

```html
<script src="https://elements-dev.your-domain.test/sdk/magpie.js"></script>
<script>
  const magpie = new Magpie("pk_test_your_key", {
    componentsUrl: "https://elements-dev.your-domain.test"
  });
</script>
```

### Step 3 — Verify the setup

```bash
# Components health check
curl https://elements-dev.your-domain.test/api/v2/health

# Components iframe loads
curl https://elements-dev.your-domain.test/components/index.html
```

Both should return `200`.

### Debug routes (optional)

For local debugging only, enable additional diagnostic endpoints:

```env
MAGPIE_DEBUG_ROUTES=true
```

This enables:
- `GET /api/v2/debug` — tests connectivity to the upstream Magpie API
- `POST /api/v2/echo` — echoes back headers and body for request inspection

**Never enable debug routes in production.**

---

## 14. Production Setup

### Checklist

- [ ] `APP_URL` set to your production components domain (e.g. `https://components.magpie.im`)
- [ ] `MAGPIE_API_URL` set to `https://api.magpie.im/v2`
- [ ] `MAGPIE_EMBED_ALLOWED_ORIGINS` lists all origins that will embed the iframe
- [ ] `MAGPIE_API_ALLOWED_ORIGINS` includes `https://components.magpie.im` (the iframe's own origin)
- [ ] `MAGPIE_DEBUG_ROUTES=false`
- [ ] HTTPS enforced on both the components server and all merchant pages
- [ ] Rate limiting configured (default: 300 requests/minute/IP on `/api/v2/sources`)
- [ ] Secret keys (`sk_live_...`) are only used server-side, never in frontend code

### Deployment

After any environment variable change, redeploy or restart your application so the new config takes effect. On Laravel Cloud or Forge, trigger a new deployment after updating env vars.

---

## 15. Security Model

### Card data isolation

Card data is entered directly inside cross-origin iframes served from `https://components.magpie.im`. Your page's JavaScript cannot read the contents of these iframes due to the browser's same-origin policy. Card data never exists in your DOM or your JavaScript.

### Key flow

1. You initialize `new Magpie("pk_live_...")` with your public key.
2. The SDK sends the public key to the iframe via `postMessage` during `INIT`.
3. The iframe uses the key to authenticate against `/api/v2/sources` using HTTP Basic Auth: `Authorization: Basic <base64(pk_live_key:)>`.
4. The proxy forwards the key — unchanged — to the upstream Magpie API.
5. Your secret key (`sk_live_...`) is never involved in source creation.

### Origin validation

- **Iframe embedding**: Controlled by CSP `frame-ancestors` header, set per-response by the `AllowIframeEmbedding` middleware. Merchants not on the allowlist cannot embed the iframe.
- **API calls**: The `VerifyRequestOrigin` middleware checks the `Origin` (or `Referer`) header on every call to `/api/v2/sources`. Origins not on the allowlist receive a `403`.
- **postMessage**: Both the SDK and the iframe validate `event.origin` before processing any message.

### Card data in logs

The backend uses `CardDataMasker` on all log entries. Card numbers are logged as `483442******4534`. CVCs are logged as `***`. Authorization headers are logged as `[REDACTED]`. No plaintext card data or API keys appear in logs.

### HTTPS

All communication between the browser, the components server, and the Magpie API must be over HTTPS. HTTP is not supported in production.

---

## 16. Error Handling & Troubleshooting

### Common errors

**`403 Origin not allowed`**

Your page's origin is not on the allowlist.

- Add your origin to `MAGPIE_EMBED_ALLOWED_ORIGINS` (for iframe embedding) and `MAGPIE_API_ALLOWED_ORIGINS` (for API calls).
- If using `MAGPIE_USE_DB_ALLOWED_ORIGINS=true`, run `php artisan magpie:allow-origin <key> <origin>`.
- Redeploy after changing env vars.

**`401 Missing Authorization header`**

The `apiKey` was not passed to `new Magpie(...)`, or the `INIT` message was not received by the iframe before `createSource()` was called.

- Ensure you pass your public key to `new Magpie("pk_live_...")`.
- Ensure `mount()` is called before `createSource()`.

**`"The API key doesn't have permissions to perform the request."`**

The key was received but rejected by the upstream Magpie API.

- Verify you are using a **public** key (`pk_live_...` or `pk_test_...`), not a secret key.
- Verify the key is active and associated with the correct Magpie account.

**`422 Validation failed`**

The request is missing required fields or contains invalid data. Common causes:

- Missing `name` field in `createSource()` call
- Missing or incomplete `redirect` object (success, fail, notify URLs are all required)
- Card fields (number, expiry, CVC) not filled in

Check the response body for the specific field that failed validation.

**`502 Connection error`**

The proxy could not reach the upstream Magpie API (`api.magpie.im`).

- Check `MAGPIE_API_URL` is set correctly.
- Verify outbound connectivity from your server to `api.magpie.im`.

**Iframe loads but card fields are blank**

- Check the browser console for CSP or CORS errors.
- Ensure the iframe `src` URL (`/components/index.html`) returns `200`.
- Ensure `componentsUrl` in `new Magpie(...)` matches the server serving the components.

**`createSource()` hangs and never resolves**

- Check that `mount()` was called for all three elements before calling `createSource()`.
- Open the browser devtools Network tab and look for a failed request to `/api/v2/sources`.
- Check the browser console for `postMessage` origin mismatch errors.

### Smoke tests

```bash
# 1. Health check
curl https://components.magpie.im/api/v2/health
# → {"status":"ok"}

# 2. Components iframe
curl -I https://components.magpie.im/components/index.html
# → HTTP/2 200

# 3. Source creation (replace key and values)
curl -X POST https://components.magpie.im/api/v2/sources \
  -H "Authorization: Basic $(echo -n 'pk_test_your_key:' | base64)" \
  -H "Content-Type: application/json" \
  -H "Origin: https://components.magpie.im" \
  -d '{
    "type": "card",
    "card": {
      "name": "Test User",
      "number": "4242424242424242",
      "exp_month": 12,
      "exp_year": 2028,
      "cvc": "123"
    },
    "redirect": {
      "success": "https://your-site.com/success",
      "fail": "https://your-site.com/fail",
      "notify": "https://your-site.com/notify"
    }
  }'
# → {"object":"source","id":"src_..."}
```

---

## 17. Full Working Example

A complete, copy-paste-ready integration with styling and error handling.

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    body {
      font-family: system-ui, -apple-system, sans-serif;
      background: #F9FAFB;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }

    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.08);
      padding: 32px;
      width: 100%;
      max-width: 420px;
    }

    h2 {
      margin: 0 0 24px;
      font-size: 20px;
      font-weight: 600;
      color: #111827;
    }

    label {
      display: block;
      margin-bottom: 4px;
      font-size: 13px;
      font-weight: 500;
      color: #374151;
    }

    .field {
      border: 1px solid #D1D5DB;
      border-radius: 8px;
      padding: 10px 12px;
      height: 44px;
      margin-bottom: 16px;
      transition: border-color 0.15s;
    }

    .field:focus-within {
      border-color: #6366F1;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    button {
      width: 100%;
      padding: 12px;
      background: #6366F1;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 8px;
    }

    button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .error {
      color: #DC2626;
      font-size: 13px;
      margin-top: 12px;
      min-height: 20px;
    }

    .success {
      color: #059669;
      font-size: 13px;
      margin-top: 12px;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Payment details</h2>

    <label>Card number</label>
    <div class="field" id="card-number"></div>

    <div class="row">
      <div>
        <label>Expiry</label>
        <div class="field" id="card-expiry"></div>
      </div>
      <div>
        <label>CVC</label>
        <div class="field" id="card-cvc"></div>
      </div>
    </div>

    <button id="pay-btn">Pay ₱100.00</button>
    <div class="error" id="error-msg"></div>
    <div class="success" id="success-msg"></div>
  </div>

  <script src="https://components.magpie.im/sdk/magpie.js"></script>
  <script>
    const magpie = new Magpie("pk_live_your_key_here");
    const elements = magpie.elements();

    const cardStyle = {
      style: {
        base: {
          fontFamily: "system-ui, -apple-system, sans-serif",
          fontSize: "15px",
          color: "#111827",
          "::placeholder": { color: "#9CA3AF" }
        }
      }
    };

    const cardNumber = elements.create("cardNumber", cardStyle);
    const cardExpiry = elements.create("cardExpiry", cardStyle);
    const cardCvc    = elements.create("cardCvc", cardStyle);

    cardNumber.mount("#card-number");
    cardExpiry.mount("#card-expiry");
    cardCvc.mount("#card-cvc");

    const payBtn    = document.getElementById("pay-btn");
    const errorMsg  = document.getElementById("error-msg");
    const successMsg = document.getElementById("success-msg");

    payBtn.addEventListener("click", async () => {
      errorMsg.textContent = "";
      successMsg.textContent = "";
      payBtn.disabled = true;
      payBtn.textContent = "Processing…";

      try {
        const source = await cardNumber.createSource({
          name: "Cardholder Name",  // Required field
          redirect: {
            success: "https://your-site.com/checkout/success",
            fail: "https://your-site.com/checkout/fail",
            notify: "https://your-site.com/checkout/notify"
          }
        });

        // Send source.id to your server to complete the charge
        successMsg.textContent = `Source created: ${source.id}`;

        // Example: send to your backend
        // await fetch("/your-server/charge", {
        //   method: "POST",
        //   headers: { "Content-Type": "application/json" },
        //   body: JSON.stringify({ source: source.id, amount: 10000 })
        // });

      } catch (err) {
        errorMsg.textContent = err?.message || "An unexpected error occurred.";
      } finally {
        payBtn.disabled = false;
        payBtn.textContent = "Pay ₱100.00";
      }
    });
  </script>
</body>
</html>
```

---

*For questions or issues, contact the Magpie integrations team or open a support ticket.*
