## PHP PDO Parameter Binding
- **NEVER** use `bindParam()` - it binds by reference and can cause unexpected behavior
- **ALWAYS** use `bindValue()` for explicit value binding and `prepare()` with named placeholders to avoid SQL injection attacks.