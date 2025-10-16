# Customer Pre-Order Notification API Documentation

## Overview

This API enables administrators to send per-customer-preorder notifications for:
- **Ready notifications**: When orders are ready for pickup or delivery (typically for fully paid orders)
- **Balance notifications**: To request customers complete remaining payment with a reason

**Channels supported**: Email and SMS (no in-app notifications or payment links)
**Key feature**: All notifications are persisted in the database with per-channel delivery status tracking.

## Configuration

### Environment Variables Required

```bash
# Email Configuration
MAIL_FROM_ADDRESS=noreply@gtechsolar.com
MAIL_FROM_NAME="G-Tech Solar"
MAIL_REPLY_TO=support@gtechsolar.com

# SMS Configuration (Termii)
SMS_PROVIDER=termii
SMS_API_KEY=your_termii_api_key
SMS_SENDER_ID=GTechSolar

# Brand Configuration
BRAND_NAME="G-Tech Solar"
```

### Mail Configuration
Configure your email provider in `config/mail.php` or use environment variables for SMTP settings.

## API Endpoints

### 1. Send Notification

**Endpoint**: `POST /api/admin/customer-pre-orders/{id}/notify`
**Authorization**: Admin only (TODO: Implement RBAC permission: `preorder.notify`)

#### Request Body

```json
{
  "mode": "ready|balance",
  "channels": ["email", "sms"],
  "subject": "Your order is ready!",
  "message": "Hello {{customer_name}}, your pre-order {{pre_order_number}} for {{product_name}} is ready for {{fulfillment_method}}.",
  "fulfillment_method": "pickup|delivery",
  
  // Required for balance mode
  "payment_deadline": "2025-10-20",
  "reason": "Early arrival — stock is coming sooner",
  
  // Required for ready mode
  "ready_date": "2025-10-15",
  "override_payment_check": false,
  
  // Required for pickup fulfillment
  "pickup_location": "Main Warehouse, Ikeja",
  
  // Required for delivery fulfillment (if not in customer record)
  "shipping_address": "123 Customer Street",
  "city": "Lagos",
  "state": "Lagos State"
}
```

#### Validation Rules

**Common fields:**
- `mode`: Required, must be "ready" or "balance"
- `channels`: Required array, minimum 1 item, each must be "email" or "sms"
- `subject`: Required string, max 255 characters
- `message`: Required string
- `fulfillment_method`: Required, must be "pickup" or "delivery"

**Balance mode specific:**
- `payment_deadline`: Required, must be date in YYYY-MM-DD format, must be in future
- `reason`: Required string, max 500 characters
- Pre-order must have `remaining_amount > 0`

**Ready mode specific:**
- `ready_date`: Required, must be date in YYYY-MM-DD format
- Pre-order must have `payment_status = 'fully_paid'` (unless `override_payment_check = true`)

**Fulfillment method specific:**
- Pickup: `pickup_location` required
- Delivery: `shipping_address`, `city`, `state` required (if not already in customer record)

#### Response

**Success (200)**:
```json
{
  "success": true,
  "data": {
    "notification_id": 987,
    "customer_preorder_id": 123,
    "mode": "balance",
    "channels": [
      {
        "channel": "email",
        "status": "sent",
        "provider_message_id": "msg_12345",
        "error": null
      },
      {
        "channel": "sms",
        "status": "queued",
        "provider_message_id": null,
        "error": null
      }
    ],
    "created_at": "2025-10-13T12:34:56Z"
  }
}
```

**Error Responses**:
- `400`: Validation error (missing required fields, invalid mode/channel, business rule violations)
- `404`: Customer pre-order not found
- `403`: Insufficient permissions
- `500`: Server error

### 2. Get Notifications for Pre-Order

**Endpoint**: `GET /api/admin/customer-pre-orders/{id}/notifications`

#### Query Parameters
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 50)

#### Response
```json
{
  "success": true,
  "data": [
    {
      "id": 987,
      "mode": "balance",
      "subject": "Complete your balance for pre-order CPO-000123",
      "channels": [
        {
          "channel": "email",
          "status": "sent",
          "sent_at": "2025-10-13T12:34:56Z",
          "error": null
        }
      ],
      "created_by": "Admin User",
      "created_at": "2025-10-13T12:34:56Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 5,
    "last_page": 1
  }
}
```

### 3. Get Notification Details

**Endpoint**: `GET /api/admin/notifications/{notification_id}`

#### Response
```json
{
  "success": true,
  "data": {
    "id": 987,
    "customer_preorder_id": 123,
    "customer_name": "John Doe",
    "pre_order_number": "CPO-000123",
    "product_name": "Solar Panel 100W",
    "mode": "balance",
    "subject": "Complete your balance",
    "message_template": "Hello {{customer_name}}, please complete...",
    "message_resolved_email": "Hello John Doe, please complete...",
    "message_resolved_sms": "Hello John Doe, please complete...",
    "fulfillment_method": "delivery",
    "payment_deadline": "2025-10-20",
    "reason": "Early arrival",
    "channels": [
      {
        "channel": "email",
        "status": "sent",
        "provider_message_id": "msg_12345",
        "error": null,
        "sent_at": "2025-10-13T12:34:56Z"
      }
    ],
    "created_by": "Admin User",
    "created_at": "2025-10-13T12:34:56Z"
  }
}
```

### 4. Resend Notification

**Endpoint**: `POST /api/admin/notifications/{notification_id}/resend`

#### Request Body
```json
{
  "channels": ["email", "sms"]  // Optional: specify channels to resend
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "notification_id": 987,
    "channels": [
      {
        "channel": "email",
        "status": "sent",
        "provider_message_id": "msg_67890",
        "error": null
      }
    ],
    "resent_at": "2025-10-13T14:20:00Z"
  }
}
```

### 5. Get Available Merge Tags

**Endpoint**: `GET /api/admin/notifications/merge-tags`

#### Response
```json
{
  "success": true,
  "data": {
    "merge_tags": {
      "{{customer_name}}": "Customer first and last name",
      "{{product_name}}": "Pre-order product name",
      "{{pre_order_number}}": "Pre-order number",
      "{{quantity}}": "Order quantity",
      "{{remaining_amount}}": "Formatted remaining balance",
      "{{currency}}": "Currency code (e.g., NGN)",
      "{{payment_deadline}}": "Payment deadline (balance mode)",
      "{{reason}}": "Reason for balance request (balance mode)",
      "{{fulfillment_method}}": "pickup or delivery",
      "{{pickup_location}}": "Pickup location (pickup mode)",
      "{{shipping_address}}": "Shipping address (delivery mode)",
      "{{shipping_city}}": "Shipping city (delivery mode)",
      "{{shipping_state}}": "Shipping state (delivery mode)",
      "{{ready_date}}": "Ready date (ready mode)"
    },
    "usage": "Use merge tags in your message template by wrapping them in double curly braces, e.g., {{customer_name}}",
    "example": "Hello {{customer_name}}, your pre-order {{pre_order_number}} for {{product_name}} is ready for {{fulfillment_method}}."
  }
}
```

## Merge Tags

Use these merge tags in your message templates:

| Tag | Description | Available In |
|-----|-------------|--------------|
| `{{customer_name}}` | Customer's full name | All modes |
| `{{product_name}}` | Pre-order product name | All modes |
| `{{pre_order_number}}` | Pre-order number | All modes |
| `{{quantity}}` | Order quantity | All modes |
| `{{remaining_amount}}` | Formatted remaining balance | All modes |
| `{{currency}}` | Currency code (e.g., NGN) | All modes |
| `{{fulfillment_method}}` | "pickup" or "delivery" | All modes |
| `{{payment_deadline}}` | Payment deadline date | Balance mode |
| `{{reason}}` | Reason for balance request | Balance mode |
| `{{ready_date}}` | Ready date | Ready mode |
| `{{pickup_location}}` | Pickup location | Pickup fulfillment |
| `{{shipping_address}}` | Shipping address | Delivery fulfillment |
| `{{shipping_city}}` | Shipping city | Delivery fulfillment |
| `{{shipping_state}}` | Shipping state | Delivery fulfillment |

## Example Requests

### Balance Reminder
```bash
curl -X POST "/api/admin/customer-pre-orders/123/notify" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "mode": "balance",
    "channels": ["email", "sms"],
    "subject": "Complete your balance for pre-order {{pre_order_number}}",
    "message": "Hello {{customer_name}}, your pre-order {{pre_order_number}} for {{product_name}} (Qty: {{quantity}}) has an early arrival! Please complete the remaining balance of {{remaining_amount}} by {{payment_deadline}}. Reason: {{reason}}. Please log in to your G-Tech Solar app/account to complete your payment.",
    "payment_deadline": "2025-10-20",
    "reason": "Early arrival — stock is coming sooner",
    "fulfillment_method": "delivery"
  }'
```

### Ready for Pickup
```bash
curl -X POST "/api/admin/customer-pre-orders/123/notify" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "mode": "ready",
    "channels": ["email"],
    "subject": "Ready for pickup: {{product_name}}",
    "message": "Hello {{customer_name}}, great news! Your pre-order {{pre_order_number}} for {{product_name}} (Qty: {{quantity}}) is now ready for pickup at {{pickup_location}}. Ready date: {{ready_date}}. Please bring your ID and order confirmation.",
    "ready_date": "2025-10-15",
    "fulfillment_method": "pickup",
    "pickup_location": "Main Warehouse, Ikeja"
  }'
```

## Provider Integration

### Email Provider
- Uses Laravel's built-in Mail system
- Configure via `config/mail.php` or environment variables
- Supports SMTP, Mailgun, SendGrid, etc.

### SMS Provider (Termii)
- API endpoint: `https://api.ng.termii.com/api/sms/send`
- Automatically formats Nigerian phone numbers
- Optimizes messages for SMS length (max 480 chars)
- Converts special characters to GSM charset

### Webhooks
Configure your providers to send delivery status webhooks to:
- **Email**: `POST /api/webhooks/email/provider`
- **SMS**: `POST /api/webhooks/sms/provider`

## Database Schema

### notifications table
- `id`: Primary key
- `customer_preorder_id`: Foreign key to customer_pre_orders
- `mode`: 'ready' or 'balance'
- `channels`: JSON array of requested channels
- `subject`: Notification subject
- `message_template`: Original message with merge tags
- `message_resolved_email`: Resolved message for email
- `message_resolved_sms`: Resolved message for SMS
- `payment_deadline`, `reason`: Balance mode fields
- `ready_date`: Ready mode field
- `fulfillment_method`: 'pickup' or 'delivery'
- `pickup_location`, `shipping_address`, `city`, `state`: Address fields
- `created_by_admin_id`: Admin who sent the notification
- `created_at`, `updated_at`: Timestamps

### notification_channels table
- `id`: Primary key
- `notification_id`: Foreign key to notifications
- `channel`: 'email' or 'sms'
- `status`: 'queued', 'sent', or 'failed'
- `provider_message_id`: Provider's message ID
- `error`: Error message if failed
- `sent_at`: Timestamp when sent
- `created_at`, `updated_at`: Timestamps

## Security Features

- Admin authentication required (TODO: Implement RBAC)
- Input sanitization and validation
- Rate limiting (recommended: 60 requests/min per admin, 5 notifications/hour per order)
- Idempotency support via `Idempotency-Key` header
- Audit logging of all notification activities

## Best Practices

1. **Message Templates**: Include the mandatory payment instruction for balance mode: "Please log in to your G-Tech Solar app/account to complete your payment."

2. **SMS Optimization**: Keep messages concise as they're automatically truncated at 480 characters.

3. **Error Handling**: Always check the response for channel-specific errors.

4. **Rate Limiting**: Implement reasonable delays between notifications to the same customer.

5. **Testing**: Use the merge tags endpoint to validate your templates before sending.

## Error Codes

| Code | Description |
|------|-------------|
| `validation_error` | Invalid request data |
| `not_found` | Customer pre-order not found |
| `forbidden` | Insufficient permissions |
| `server_error` | Internal server error |

## Support

For technical support or feature requests, contact the development team with:
- API endpoint used
- Request payload (redacted sensitive data)
- Error response received
- Expected behavior