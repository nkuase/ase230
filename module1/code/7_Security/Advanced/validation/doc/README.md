# Input Validation Examples

This project demonstrates secure input validation in PHP using both basic validation techniques and a custom Validator class.

## Files Overview

### Core Files
- `index.php` - Main navigation and overview
- `Validator.php` - Core validation class with method chaining

### Examples
1. `simple_validation.php` - Basic validation without using classes
2. `basic_example.php` - Simple validator class usage
3. `registration_form.php` - Complete registration form with comprehensive validation
4. `api_validation.php` - JSON API validation example
5. `file_upload.php` - Secure file upload validation
6. `custom_validation.php` - Advanced custom validation rules

### Directories
- `uploads/` - Directory for uploaded files (created automatically)

## Key Learning Concepts

### Security Principles
- **Never trust user input** - Always validate on server-side
- **Sanitize and validate** - Clean data before processing
- **Use proper error handling** - Show helpful but not revealing errors
- **Prevent common attacks** - SQL injection, XSS, file upload exploits

### Validation Types Demonstrated
- **Required fields** - Check for empty values
- **Format validation** - Email, URL, phone number patterns
- **Length constraints** - Minimum and maximum character limits  
- **Numeric ranges** - Age, quantity, price validations
- **Pattern matching** - Regular expressions for complex formats
- **File uploads** - Size, type, extension validation
- **Custom business rules** - Domain-specific validation logic

### PHP Features Used
- `filter_var()` with `FILTER_VALIDATE_EMAIL` and `FILTER_VALIDATE_URL`
- `preg_match()` for regular expression validation
- `htmlspecialchars()` for XSS prevention
- `move_uploaded_file()` for secure file handling
- Method chaining for clean validation code
- `$_FILES` superglobal for file upload handling

## How to Use

1. **Start with `index.php`** - Overview of all examples
2. **Follow the progression** - From simple to advanced examples
3. **Test with invalid data** - See how validation catches errors
4. **Read the code comments** - Understand the security principles
5. **Modify and experiment** - Try adding your own validation rules

## Best Practices Demonstrated

1. **Server-side validation** - Never rely on client-side only
2. **Method chaining** - Clean, readable validation code
3. **Comprehensive error messages** - Help users fix issues
4. **Security-first approach** - Prevent common vulnerabilities
5. **Reusable validation logic** - DRY principle applied

## Testing Suggestions

### Valid Test Data
- Username: john_doe
- Email: john@example.com  
- Password: MySecure123!
- Age: 25
- Phone: (555) 123-4567
- Website: https://example.com

### Invalid Test Data
- Empty required fields
- Email: invalid-email-format
- Password: weak (no uppercase/numbers/special chars)
- Age: 17 (if 18+ required)
- Phone: abc-123-def
- File: Upload .exe or oversized files

## Real-World Applications

This validation system can be used for:
- **User registration forms**
- **Contact forms**  
- **E-commerce checkout**
- **API endpoints**
- **File upload systems**
- **Admin panels**

## Security Notes

**Remember**: This is an educational example. In production:
- Use prepared statements for database queries
- Implement rate limiting for forms
- Add CSRF protection
- Use HTTPS for sensitive data
- Log validation failures for security monitoring
- Consider additional validation libraries like Respect/Validation

## Author
Created for ASE230 - Application Security Engineering
Northern Kentucky University
