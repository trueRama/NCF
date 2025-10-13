# Event Schemas

This directory contains JSON Schema definitions for all NCF events.

## Schema Files

### base-event.schema.json
The foundational schema that all NCF events must extend. It defines the common structure:
- `eventType`: Unique event identifier
- `version`: Schema version
- `timestamp`: When the event occurred
- `eventId`: Unique event instance ID
- `payload`: Event-specific data
- `metadata`: Additional context (source, correlation, etc.)

### notification-event.schema.json
Schema specific to notification events. Extends the base event schema with notification-specific payload requirements:
- `notificationId`: Unique notification identifier
- `recipientId`: Recipient user ID
- `channel`: Communication channel (email, sms, push, in-app)
- `message`: Notification content (subject, body, priority)
- `status`: Notification status (pending, sent, delivered, failed, read)

## Using Schemas

### Validation
Schemas can be used to validate event payloads before publishing:

```javascript
// Example using AJV in JavaScript
const Ajv = require('ajv');
const ajv = new Ajv();
const schema = require('./schemas/notification-event.schema.json');
const validate = ajv.compile(schema);

const event = { /* your event data */ };
const valid = validate(event);
if (!valid) {
  console.error(validate.errors);
}
```

```python
# Example using jsonschema in Python
import json
import jsonschema

with open('schemas/notification-event.schema.json') as f:
    schema = json.load(f)

event = { }  # your event data
jsonschema.validate(instance=event, schema=schema)
```

## Schema Evolution

When updating schemas:
1. **Increment the version number** following semantic versioning
2. **Maintain backward compatibility** when possible
3. **Document breaking changes** in the event catalog
4. **Provide migration guides** for consumers

### Version Guidelines
- **Patch (1.0.x)**: Bug fixes, clarifications
- **Minor (1.x.0)**: Backward-compatible additions
- **Major (x.0.0)**: Breaking changes
