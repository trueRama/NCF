# Event Definitions

This directory contains event type definitions and documentation for all NCF events.

## Available Event Types

### Notification Events

#### notification.created
Triggered when a new notification is created in the system.

**Use Case**: Track notification creation for analytics and auditing

**Payload Fields**:
- `notificationId`: Unique notification identifier
- `recipientId`: User receiving the notification
- `channel`: Communication channel (email, sms, push, in-app)
- `message`: Notification content (subject, body, priority)

#### notification.sent
Triggered when a notification is successfully sent.

**Use Case**: Confirm successful delivery to communication channel

**Payload Fields**: Same as notification.created, plus:
- `status`: Should be "sent"
- `sentAt`: Timestamp when sent

#### notification.failed
Triggered when a notification fails to send.

**Use Case**: Track failures for retry logic and alerting

**Payload Fields**: Same as notification.created, plus:
- `status`: Should be "failed"
- `error`: Error details (code, message, retryable)

#### notification.read
Triggered when a user reads/acknowledges a notification.

**Use Case**: Track engagement metrics

**Payload Fields**: Same as notification.created, plus:
- `status`: Should be "read"
- `readAt`: Timestamp when read

## Event Naming Convention

Events follow the pattern: `{domain}.{action}`

- **domain**: The business domain (e.g., notification, user, system)
- **action**: Past-tense verb describing what happened (e.g., created, updated, deleted, sent)

Examples:
- `notification.sent`
- `user.registered`
- `system.started`
