# Calendar API Refactoring

## Overview
The Calendar API has been refactored to follow the framework's established patterns, breaking down the monolithic `api.php` file into separate, purpose-specific files.

## New Structure

### Framework-Compliant Files
- **`get.php`** - Handles all GET requests (data retrieval)
- **`post.php`** - Handles POST requests (create operations)
- **`put.php`** - Handles PUT requests (update operations)
- **`delete.php`** - Handles DELETE requests (removal operations)
- **`events_ajax.php`** - Handles AJAX operations

### Backward Compatibility
- **`api.php`** - Legacy compatibility layer that redirects to appropriate files
- **`api_monolithic_backup.php`** - Backup of original monolithic implementation

## Benefits of Refactoring

1. **Framework Consistency** - Now follows the same pattern as Users and Leads modules
2. **Separation of Concerns** - Each file has a single responsibility
3. **Improved Maintainability** - Easier to locate and modify specific functionality
4. **Enhanced Security** - Framework conventions provide consistent security patterns
5. **Better Testing** - Individual components can be tested in isolation
6. **CalDAV Ready** - Modular structure aligns with CalDAV server implementation patterns

## API Endpoints

### GET Operations (`get.php`)
- `GET /calendar/get.php?action=events&start=YYYY-MM-DD&end=YYYY-MM-DD` - Get calendar events
- `GET /calendar/get.php?action=event&id=123` - Get single event
- `GET /calendar/get.php?action=today&limit=10` - Get today's events
- `GET /calendar/get.php?action=stats&date=YYYY-MM-DD` - Get event statistics
- `GET /calendar/get.php?action=types` - Get event types
- `GET /calendar/get.php?action=priorities` - Get priority levels

### POST Operations (`post.php`)
- `POST /calendar/post.php?action=create` - Create new event
- `POST /calendar/post.php?action=from_next_action` - Create event from Next Action

### PUT Operations (`put.php`)
- `PUT /calendar/put.php?action=update&id=123` - Update event
- `PUT /calendar/put.php?action=move&id=123` - Move event (drag & drop)

### DELETE Operations (`delete.php`)
- `DELETE /calendar/delete.php?action=delete&id=123` - Delete event

### AJAX Operations (`events_ajax.php`)
- `POST /calendar/events_ajax.php` with `action=quick_create` - Quick create event
- `POST /calendar/events_ajax.php` with `action=toggle_status` - Toggle event status
- `POST /calendar/events_ajax.php` with `action=bulk_delete` - Bulk delete events
- `POST /calendar/events_ajax.php` with `action=get_upcoming` - Get upcoming events
- `POST /calendar/events_ajax.php` with `action=search` - Search events

## Migration Guide

### For Existing Code
No changes required immediately - the legacy `api.php` file provides backward compatibility.

### For New Development
Use the specific endpoint files directly:
```javascript
// Old way (still works)
fetch('/calendar/api.php?action=events')

// New way (recommended)
fetch('/calendar/get.php?action=events')
```

### For CalDAV Integration
The new modular structure provides a clean foundation for CalDAV server implementation:
- Use `get.php` for CalDAV PROPFIND operations
- Use `post.php` for CalDAV PUT operations (creating events)
- Use `put.php` for CalDAV POST operations (updating events)
- Use `delete.php` for CalDAV DELETE operations

## Security Features

All files maintain the framework's security patterns:
- Session-based authentication
- CSRF token verification for state-changing operations
- User access control
- Input validation and sanitization
- Error logging and handling

## Testing

Each file can now be tested independently:
```bash
# Test GET operations
curl -X GET "http://localhost/calendar/get.php?action=events"

# Test POST operations
curl -X POST "http://localhost/calendar/post.php?action=create" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Event","start_datetime":"2024-01-01 10:00:00","event_type":"meeting","csrf_token":"..."}'
```

## Future Enhancements

This refactored structure enables:
1. **CalDAV Server Integration** - Clean separation for CalDAV protocol implementation
2. **API Versioning** - Easy to add v2 endpoints alongside existing structure
3. **Microservices** - Individual files can be deployed as separate services
4. **Enhanced Caching** - Specific endpoints can have targeted caching strategies
5. **Rate Limiting** - Different limits can be applied to different operation types