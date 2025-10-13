# NCF Event Repository Documentation

## Introduction

The NCF Event Repository is a centralized location for managing event definitions, schemas, and examples for the Notification and Communication Framework (NCF).

## Repository Structure

```
NCF/
├── README.md                    # Main repository documentation
├── events/                      # Event type definitions
│   └── README.md               # Event catalog and documentation
├── schemas/                     # JSON schemas for event validation
│   ├── base-event.schema.json  # Base schema for all events
│   └── notification-event.schema.json  # Notification-specific events
├── examples/                    # Example event payloads
│   ├── notification-created.json
│   └── notification-sent.json
└── docs/                       # Additional documentation
    └── overview.md             # This file
```

## Event-Driven Architecture

NCF uses an event-driven architecture where services communicate through standardized events. This approach provides:

- **Loose Coupling**: Services don't need direct knowledge of each other
- **Scalability**: Events can be processed asynchronously
- **Auditability**: Complete event history for compliance and debugging
- **Flexibility**: Easy to add new event consumers without modifying producers

## Event Lifecycle

1. **Event Creation**: A service creates an event when something significant happens
2. **Event Publishing**: The event is published to the event bus/stream
3. **Event Processing**: Interested services consume and process the event
4. **Event Storage**: Events are stored for audit and replay purposes

## Schema Validation

All events must conform to the schemas defined in the `schemas/` directory. This ensures:

- Consistent event structure across all services
- Type safety and validation
- Backward compatibility through versioning
- Clear contracts between event producers and consumers

## Best Practices

1. **Use Semantic Naming**: Event names should clearly describe what happened (past tense)
2. **Version Your Schemas**: Include version numbers to handle schema evolution
3. **Include Metadata**: Always add correlation IDs for tracing
4. **Keep Payloads Focused**: Only include relevant data in the payload
5. **Document Your Events**: Update the event catalog when adding new event types

## Working with Events

### Creating a New Event Type

1. Define the schema in `schemas/` extending from `base-event.schema.json`
2. Document the event in `events/README.md`
3. Provide example payloads in `examples/`
4. Update this documentation if needed

### Validating Events

Use JSON schema validators to ensure events conform to their schemas:

```bash
# Example using ajv-cli
ajv validate -s schemas/notification-event.schema.json -d examples/notification-sent.json
```

## Support

For questions or issues related to the NCF Event Repository, please contact the NCF team.
