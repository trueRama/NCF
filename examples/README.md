# Event Examples

This directory contains example event payloads for each event type in the NCF system.

## Available Examples

### Notification Events

- **notification-created.json**: Example of a notification creation event
- **notification-sent.json**: Example of a successful notification send event

## Using Examples

These examples serve multiple purposes:

1. **Documentation**: Show developers what a valid event looks like
2. **Testing**: Use as test data in unit and integration tests
3. **Validation**: Verify that your events match the expected format
4. **Development**: Copy and modify for quick prototyping

## Example Structure

Each example follows the base event schema with:
- Real-world-like values
- Proper UUID formatting
- ISO 8601 timestamps
- Complete metadata

## Adding New Examples

When adding examples for new event types:
1. Follow the naming convention: `{domain}-{action}.json`
2. Use realistic but fictional data
3. Include all required fields from the schema
4. Add helpful comments if the payload is complex
5. Ensure the example validates against its schema

## Validation

You can validate these examples against their schemas:

```bash
# Using ajv-cli (requires: npm install -g ajv-cli)
ajv validate -s schemas/notification-event.schema.json -r schemas/base-event.schema.json -d examples/notification-sent.json
```
