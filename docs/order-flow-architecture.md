# WooCommerce Order Flow Architecture

## Overview

This document describes the order lifecycle on the EPOS.com platform — from adding items to cart through payment completion and post-order processing.

The platform uses WooCommerce as its e-commerce engine, with custom checkout fields, multiple payment gateways via the **zippy-pay** plugin, a custom order management dashboard via the **zippy-core** plugin, and integrations with HubSpot CRM, Facebook CAPI, and Google Tag Manager.

---

## Order Lifecycle

### 1. Add to Cart

When a customer adds a product to the cart:

- WooCommerce stores the item in the session-based cart
- A **GTM `add_to_cart` event** is pushed to the dataLayer for GA4 tracking
- The customer is redirected to the cart page
- Duplicate add-to-cart is prevented — quantity is updated instead

The cart page uses a custom Flatsome shortcode override with a modified "Return to Shop" redirect pointing to `/my/bluetap`.

---

### 2. Checkout

The checkout page presents customized billing fields:

- **Full Name** — single field, split into first/last name on submission
- **Company Name** — required
- **Email** — validated against MX records to verify the domain exists
- **Phone** — uses intl-tel-input for international country code selection
- **Referral Code** — shown only when the cart contains the BlueTap product

Order notes are removed. A **Facebook `InitiateCheckout` pixel event** fires on form submission (client-side).

---

### 3. Order Creation

When the customer clicks "Place Order", WooCommerce creates the order via the `woocommerce_checkout_create_order` hook:

1. A `WC_Order` is created with status `pending`
2. Custom metadata is saved:
   - `referral_code` — from the checkout form (BlueTap orders only)
   - UTM attribution data (`_wc_order_attribution_utm_source`, `_utm_medium`, `_utm_campaign`)
3. **HubSpot Contact Sync** is triggered immediately (see [HubSpot Integration](#hubspot-integration) below)

---

### 4. Payment Processing

After order creation, WooCommerce hands off to the selected payment gateway. The platform supports three gateways through the **zippy-pay** plugin:

#### PayNow (Singapore)

1. Builds an encrypted payload with order amount
2. Calls Zippy API: `POST rest.zippy.sg/v1/payment/paynow/qr`
3. Schedules a **WP-Cron background job** (`zippy_check_paynow_payment_task`) — checks every 60s, up to 40 retries
4. Redirects customer to QR code payment page
5. On callback (`?wc-api=zippy_paynow_transaction`): verifies status via `GET /v1/payment/paynow/transaction`
6. On success → `$order->payment_complete()` → redirects to thank-you page

#### Antom (Alipay)

1. Redirects to checkout payment URL, then to `/antom-payment` page
2. Creates payment session via Zippy API: `POST rest.zippy.sg/v1/payment/antom/ecommerce/session`
3. Stores `paymentRequestId` in order meta
4. Schedules **WP-Cron background job** (`zippy_check_antom_payment_task`) — same 60s interval, 40 retries
5. Background job validates via: `POST /v1/payment/antom/ecommerce/validate`
6. On success → `$order->payment_complete()` → order status set to `processing`

#### 2C2P (Credit Card / Touch 'n Go)

1. Requests a payment token via JWT-signed payload: `POST /paymentToken`
2. Stores `_2c2p_payment_token` and `_2c2p_invoice_no` in order meta
3. Renders the 2C2P Drop-in UI (`pgw-sdk-4.2.1.js`) on the receipt page
4. **Backend callback** (`?wc-api=zippy_2c2p_transaction`): receives JWT payload from 2C2P
   - Response code `0000` → payment success → `$order->payment_complete()`
   - Response code `0001` → pending → sets order to `on-hold`, schedules cron retry
   - Other codes → logs error, schedules retry
5. **Frontend redirect** (`?wc-api=zippy_2c2p_redirect`): inquires transaction + payment status, redirects accordingly

---

### 5. Payment Confirmed — Post-Order Processing

When `$order->payment_complete()` is called, WooCommerce transitions the order to `processing` status. This triggers multiple hooks simultaneously:

#### HubSpot: Update Contact Payment Status

**Hook:** `woocommerce_order_status_processing` (priority 10)
**File:** `updata_payment_status.php`

- Updates the HubSpot contact with `payment_status = PAID`
- Upsert logic: tries `PATCH` first, creates new contact on `404`

#### HubSpot: Create Deal

**Hook:** `woocommerce_order_status_processing` (priority 10)
**File:** `deal_information.php`

- Upserts the contact again (with full billing + UTM data) to get the HubSpot `contact_id`
- Creates a HubSpot Deal:
  - **Deal name:** `EPOS Bluetap Checkout - Order #<order_number>`
  - **Amount:** order total
  - **Pipeline:** `781854069`
  - **Deal stage:** `1142728054`
  - **Custom properties:** `pi_number`, `referral_code`, `merchat_name__payment_team_`
  - **Close date:** order completion date
- Associates the Deal to the Contact (association type ID: `3`)

#### Facebook CAPI: Server-Side Purchase Event

**Hook:** `woocommerce_order_status_processing`
**File:** `class-fb-api.php`

- Sends `Purchase` event to Meta Graph API (`v19.0`)
- Includes: order total, currency, hashed email, hashed phone, client IP, user agent
- Event ID: `PURCHASE_<order_id>` (for deduplication with client-side pixel)
- Sets `_fb_capi_sent = yes` on order meta to prevent duplicate sends

#### WooCommerce Email Notifications

- Admin receives "New Order" email
- Customer receives "Order Processing" email

---

### 6. Thank You Page

The customer lands on the order-received page showing:

- A "Payment Confirmed" success banner (if order is paid)
- Order details and customer information
- Activation timeline message
- A breadcrumb trail: Cart → Checkout → Order Complete
- **Facebook Pixel** fires a client-side `Purchase` event (with event ID `PURCHASE_<order_id>` matching the server-side event for deduplication)
- **PayNow only:** WhatsApp message template displayed for merchant contact

---

### 7. Order Management (Admin)

The **zippy-core** plugin provides a custom admin dashboard replacing the default WooCommerce orders screen. Admins can:

- **View orders** with filtering by status, date range, and pagination
- **Edit orders** — add/remove items, update quantities, apply coupons (with tax recalculation)
- **Bulk actions** — update statuses or move to trash
- **Export** — download orders as CSV or PDF
- **Generate invoices** — PDF invoices with GST (9%) calculation via Dompdf
- **Send emails** — trigger WooCommerce emails (new order, processing, completed, cancelled, failed, invoice)

All admin operations are exposed via REST API endpoints under `zippy-core/v1/`.

---

## HubSpot Integration

HubSpot is integrated at two points in the order lifecycle, using the CRM v3 API (`api.hubapi.com/crm/v3/`):

### On Order Created (`woocommerce_checkout_order_processed`)

```
Customer places order
        │
        ▼
┌─────────────────────────────────┐
│  Collect order data:            │
│  - name, email, phone, company  │
│  - address, city, state, zip    │
│  - UTM source/medium/campaign   │
│  - referral_code                │
│  - product names + quantities   │
│  - order total                  │
│  - payment_status =             │
│    "INITIATED CHECKOUT"         │
│  - lifecyclestage = "customer"  │
└────────────┬────────────────────┘
             ▼
┌─────────────────────────────────┐
│  PATCH /crm/v3/objects/contacts │
│  /{email}?idProperty=email      │
│  (Update existing contact)      │
└────────────┬────────────────────┘
             │
        ┌────┴────┐
        │  404?   │
        └────┬────┘
         yes │        no
             ▼         ▼
┌──────────────────┐  (done)
│ POST /crm/v3/    │
│ objects/contacts  │
│ (Create new)     │
└──────────────────┘
```

### On Payment Confirmed (`woocommerce_order_status_processing`)

```
Order status → processing
        │
        ├──────────────────────────────────────┐
        ▼                                      ▼
┌──────────────────────┐       ┌────────────────────────────────┐
│  Update Contact      │       │  Upsert Contact (get ID)       │
│  payment_status =    │       │  with full billing + UTM data  │
│  "PAID"              │       └───────────────┬────────────────┘
│                      │                       ▼
│  PATCH /crm/v3/      │       ┌────────────────────────────────┐
│  objects/contacts/   │       │  Create Deal                   │
│  {email}             │       │                                │
└──────────────────────┘       │  POST /crm/v3/objects/deals    │
                               │                                │
                               │  dealname: "EPOS Bluetap       │
                               │   Checkout - Order #xxx"       │
                               │  amount: order total           │
                               │  pipeline: 781854069           │
                               │  dealstage: 1142728054         │
                               │  pi_number: order number       │
                               │  referral_code: from meta      │
                               │  closedate: completion date    │
                               │                                │
                               │  Association:                  │
                               │  Contact → Deal (type 3)       │
                               └────────────────────────────────┘
```

### HubSpot Contact Properties Mapped

| HubSpot Property | Source |
|------------------|--------|
| `email` | `$order->get_billing_email()` |
| `firstname` / `lastname` | `$order->get_billing_first_name()` / `last_name()` |
| `phone` | `$order->get_billing_phone()` |
| `company` | `$order->get_billing_company()` |
| `address` | `billing_address_1 + billing_address_2` |
| `city` / `state` / `zip` / `country` | Billing fields (state resolved to name) |
| `payment_status` | `"INITIATED CHECKOUT"` → `"PAID"` |
| `lifecyclestage` | `"customer"` |
| `total_pricing` | `$order->get_total()` |
| `product_name` | Product names with quantities (e.g. `"BlueTap (x2)"`) |
| `referral_code` | From order meta |
| `message` | Customer order note |
| `utm_source` / `utm_medium` / `utm_campaign` | WC order attribution meta |
| `hs_latest_source` | `"OTHER_CAMPAIGNS"` |

---

## Complete Flow Diagram

```
┌──────────────────┐
│   ADD TO CART     │
│                   │────────────────────────► GTM: push add_to_cart
│   Hook:           │                          to dataLayer (GA4)
│   add_to_cart     │
│   _validation     │
└────────┬─────────┘
         ▼
┌──────────────────┐
│    CART PAGE      │
│                   │
│   Custom Flatsome │
│   shortcode       │
└────────┬─────────┘
         ▼
┌──────────────────┐
│  CHECKOUT PAGE    │────────────────────────► FB Pixel: InitiateCheckout
│                   │                          (client-side, on form submit)
│  Custom fields:   │
│  - Full name      │
│  - Company (req)  │
│  - Email (MX)     │
│  - Phone (intl)   │
│  - Referral code  │
└────────┬─────────┘
         ▼
┌──────────────────┐                         ┌──────────────────────────────┐
│  ORDER CREATED    │                         │        HUBSPOT CRM           │
│  (status: pending)│                         │                              │
│                   │────────────────────────►│  1. Upsert Contact           │
│  Hooks:           │  woocommerce_checkout   │     email, name, phone,      │
│  - create_order   │  _order_processed       │     company, address, UTM    │
│  - save referral  │                         │     payment_status =         │
│    code to meta   │                         │     "INITIATED CHECKOUT"     │
│  - save UTM data  │                         │     lifecyclestage =         │
│                   │                         │     "customer"               │
└────────┬─────────┘                         └──────────────────────────────┘
         ▼
┌──────────────────────────────────────────────────────────────────────┐
│                       PAYMENT GATEWAY                                │
│                                                                      │
│  ┌─────────────────┐  ┌─────────────────┐  ┌──────────────────────┐ │
│  │    PayNow (SG)   │  │  Antom (Alipay)  │  │    2C2P (CC/TNG)     │ │
│  │                  │  │                  │  │                      │ │
│  │ 1.Build payload  │  │ 1.Redirect to    │  │ 1.Request JWT token  │ │
│  │ 2.POST /paynow/  │  │   receipt page   │  │ 2.POST /paymentToken │ │
│  │   qr             │  │ 2.POST /antom/   │  │ 3.Render Drop-in UI │ │
│  │ 3.Schedule cron   │  │   session        │  │ 4.Backend callback   │ │
│  │   (60s x 40)     │  │ 3.Schedule cron   │  │   (JWT signed)       │ │
│  │ 4.Redirect to    │  │   (60s x 40)     │  │ 5.Retry cron if      │ │
│  │   QR page        │  │ 4.Redirect to    │  │   pending (0001)     │ │
│  │ 5.Callback:      │  │   /antom-payment  │  │                      │ │
│  │   wc-api=zippy_  │  │ 5.Validate:      │  │ Success: 0000        │ │
│  │   paynow_*       │  │   /antom/validate │  │ Pending: 0001        │ │
│  └────────┬─────────┘  └────────┬─────────┘  └──────────┬───────────┘ │
│           └─────────────────────┼─────────────────────────┘           │
│                                 ▼                                     │
│                   $order->payment_complete()                          │
│                   Order status → processing                          │
└─────────────────────────────────┬────────────────────────────────────┘
                                  │
         ┌────────────────────────┼──────────────────────────┐
         ▼                        ▼                          ▼
┌──────────────────┐  ┌───────────────────────┐  ┌─────────────────────┐
│   HUBSPOT CRM    │  │   FACEBOOK CAPI       │  │  WOOCOMMERCE EMAIL  │
│                  │  │   (Server-Side)        │  │                     │
│ 1.Update Contact │  │                        │  │ - New Order (admin) │
│   payment_status │  │ POST graph.facebook    │  │ - Processing        │
│   = "PAID"       │  │ .com/v19.0/{pixel_id}  │  │   (customer)        │
│                  │  │ /events                │  │                     │
│ 2.Upsert Contact │  │                        │  │ Referral code shown │
│   (get contact_  │  │ Event: Purchase        │  │ in email if present │
│   id for deal)   │  │ Value: order total     │  │                     │
│                  │  │ Event ID:              │  └─────────────────────┘
│ 3.Create Deal    │  │   PURCHASE_{order_id}  │
│   - dealname:    │  │ User: hashed email +   │
│     "EPOS        │  │   phone, IP, UA        │
│     Bluetap      │  │                        │
│     Checkout -   │  │ Meta: _fb_capi_sent =  │
│     Order #xxx"  │  │   "yes" (dedup flag)   │
│   - amount       │  │                        │
│   - pipeline     │  └───────────────────────┘
│   - dealstage    │
│   - referral_    │
│     code         │
│   - pi_number    │
│                  │
│ 4.Associate      │
│   Deal → Contact │
│   (type 3)       │
└──────────────────┘
         │
         ▼
┌──────────────────┐
│  THANK YOU PAGE   │────────────────────────► FB Pixel: Purchase
│                   │                          (client-side, event ID
│  - Success banner │                           matches CAPI for dedup)
│  - Order details  │
│  - Activation     │
│    timeline msg   │
│  - Breadcrumbs    │
│  - WhatsApp msg   │
│    (PayNow only)  │
└──────────────────┘
```

---

## Order Metadata Reference

| Meta Key | Set When | Purpose |
|----------|----------|---------|
| `referral_code` | Order created | BlueTap referral tracking |
| `_wc_order_attribution_utm_source` | Order created | UTM source attribution |
| `_wc_order_attribution_utm_medium` | Order created | UTM medium attribution |
| `_wc_order_attribution_utm_campaign` | Order created | UTM campaign attribution |
| `_fb_capi_sent` | Payment confirmed | Prevents duplicate FB CAPI events |
| `zippy_antom_transaction` | Antom payment | Transaction status object |
| `_2c2p_payment_token` | 2C2P payment | JWT payment token |
| `_2c2p_invoice_no` | 2C2P payment | Invoice number for inquiry |

---

## Key Files

| Component | Location |
|-----------|----------|
| Checkout customization | `themes/zippy-child/includes/zippy_checkout.php` |
| EPOS MY checkout | `themes/zippy-child/includes/epos_my_custom_flow/epos_checkout_custom.php` |
| Cart customization | `themes/zippy-child/includes/epos_my_custom_flow/epos_flatsome_cart.php` |
| PayNow gateway | `plugins/zippy-pay/core/paynow/zippy-paynow-gateway.php` |
| Antom gateway | `plugins/zippy-pay/core/antom/zippy-antom-gateway.php` |
| 2C2P gateway | `plugins/zippy-pay/core/tctp/zippy-2c2p-gateway.php` |
| 2C2P cron retry | `plugins/zippy-pay/core/tctp/zippy-2c2p-cron.php` |
| Orders REST API | `plugins/zippy-core/src/modules/orders/` |
| Invoice template | `plugins/zippy-core/src/modules/orders/templates/invoice-template.php` |
| HubSpot — order sync | `themes/zippy-child/includes/hubspot_intergration/order_infomation.php` |
| HubSpot — payment update | `themes/zippy-child/includes/hubspot_intergration/updata_payment_status.php` |
| HubSpot — deal creation | `themes/zippy-child/includes/hubspot_intergration/deal_information.php` |
| HubSpot — API helpers | `themes/zippy-child/includes/hubspot_intergration/services.php` |
| FB Pixel (client-side) | `themes/zippy-child/includes/fb_tracking/includes/class-fb-wc-events.php` |
| FB CAPI (server-side) | `themes/zippy-child/includes/fb_tracking/includes/class-fb-api.php` |
| GTM tracking | `themes/zippy-child/includes/gtm_tracking/includes/class-gtm-events.php` |

> All theme/plugin paths are relative to `src/wp-content/`.
