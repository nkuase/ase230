# Two-Factor Authentication (2FA) Demo - ENHANCED

A comprehensive PHP implementation of **TOTP** (Time-based One-Time Password) for educational purposes with full reset and management capabilities.

## Project Structure

```
two_factor/
├── index.html        # Enhanced interactive web demo with reset features
├── SimpleTOTP.php    # TOTP algorithm implementation (FIXED QR codes)
├── config.php        # Configuration and user management
├── setup.php         # 2FA setup endpoint (ENHANCED with force reset)
├── verify.php        # Setup verification endpoint
├── login.php         # 2FA login endpoint
├── reset.php         # 2FA reset endpoint (NEW)
├── status.php        # 2FA status check endpoint (NEW)
├── test/quick_reset.php  # Command-line reset tool
├── test/test.php         # Command-line TOTP demo
├── test/demo_complete.php # Complete command-line demo
└── README.md         # This file
```

## IMPORTANT FIXES APPLIED

**QR Code Issue Resolved**: The original code used Google Charts API for QR code generation, which was deprecated in 2012 and shut down in 2019. We've updated the code to use modern, working QR code services.

**Reset Functionality Added**: Complete 2FA reset and management system for educational and development purposes.

**What was changed:**
- `SimpleTOTP.php`: Updated `getQRCodeURL()` to use QR Server API instead of Google Charts
- `setup.php`: Enhanced with force reset capability
- Added `reset.php`: Full 2FA reset endpoint
- Added `status.php`: 2FA status checking
- Added `quick_reset.php`: Command-line reset tool
- Enhanced `index.html`: Complete web interface with reset features
- Multiple QR code services available as fallback options

**Educational Learning Point**: This demonstrates how external dependencies can break over time, the importance of using actively maintained services, and proper user account management in 2FA systems.

## How to Run

1. **Start PHP server:**
   ```bash
   cd two_factor/
   php -S localhost:8000
   ```

2. **Open browser:**
   ```
   http://localhost:8000
   ```

3. **Run the command-line TOTP test:**
   ```bash
   php test/test.php
   ```

4. **Or run the complete demo:**
   ```bash
   php test/demo_complete.php
   ```

5. **Quick reset a user's 2FA:**
   ```bash
   php test/quick_reset.php john
   php test/quick_reset.php admin
   php test/quick_reset.php --all
   ```

6. **Check 2FA status:**
   ```bash
   curl "http://localhost:8000/status.php?username=john"
   ```

## Setup Requirements

**Authenticator App Required:**
- Google Authenticator
- Microsoft Authenticator
- Authy
- 1Password

## Demo Accounts

| Username | Password    | 2FA Status |
|----------|-------------|------------|
| john     | Secret123   | Can be reset |
| admin    | Admin123    | Can be reset |

## 2FA Reset & Management

### **Problem**: 2FA Already Enabled
When users have 2FA already set up, they can't use it again or may need to reset due to:
- Lost phone
- New device
- Uninstalled authenticator app
- Want to switch authenticator apps

### **Solutions Provided:**

#### 1. **Web Interface Reset (Recommended)**
```
http://localhost:8000
```
- Check current 2FA status
- Reset 2FA with confirmation
- Reset and immediately start new setup
- Visual step-by-step process

#### 2. **API Reset Endpoint**
```bash
curl -X POST http://localhost:8000/reset.php \
  -H "Content-Type: application/json" \
  -d '{"username":"john","password":"Secret123","confirm_reset":true}'
```

#### 3. **Command Line Reset**
```bash
# Reset specific user
php quick_reset.php john

# Reset all users
php quick_reset.php --all
```

#### 4. **Force Reset During Setup**
```bash
curl -X POST http://localhost:8000/setup.php \
  -H "Content-Type: application/json" \
  -d '{"username":"john","password":"Secret123","force_reset":true}'
```

## Complete Learning Flow

### **1. Check Status**
```bash
# Basic status
curl "http://localhost:8000/status.php?username=john"

# Detailed status (with password)
curl -X POST http://localhost:8000/status.php \
  -H "Content-Type: application/json" \
  -d '{"username":"john","password":"Secret123"}'
```

### **2. Setup 2FA**
```bash
curl -X POST http://localhost:8000/setup.php \
  -H "Content-Type: application/json" \
  -d '{"username":"john","password":"Secret123"}'
```

### **3. Verify Setup**
```bash
curl -X POST http://localhost:8000/verify.php \
  -H "Content-Type: application/json" \
  -d '{"username":"john","code":"123456"}'
```

### **4. Login with 2FA**
```bash
curl -X POST http://localhost:8000/login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"john","password":"Secret123","totp_code":"123456"}'
```

### **5. Reset When Needed**
```bash
curl -X POST http://localhost:8000/reset.php \
  -H "Content-Type: application/json" \
  -d '{"username":"john","password":"Secret123","confirm_reset":true}'
```

## TOTP Algorithm Explained

### Step-by-Step Process:

1. **Generate Secret** (once per user):
   ```php
   $secret = random_bytes(16);  // 128-bit random key
   ```

2. **Calculate Time Window**:
   ```php
   $time_slice = floor(time() / 30);  // 30-second windows
   ```

3. **Generate HMAC-SHA1**:
   ```php
   $hash = hash_hmac('sha1', pack('N*', 0, $time_slice), $secret, true);
   ```

4. **Dynamic Truncation**:
   ```php
   $offset = ord($hash[19]) & 0xf;
   $code = (...) % 1000000;  // 6-digit code
   ```

## Time Window Behavior

| Time | Window | Code Example |
|------|--------|--------------|
| 14:30:00 | 29000 | 123456 |
| 14:30:29 | 29000 | 123456 |
| 14:30:30 | 29001 | 654321 |
| 14:31:00 | 29002 | 789012 |

**Key Points:**
- Codes change every **30 seconds**
- **±1 window tolerance** for clock drift
- **Cryptographically secure** (HMAC-SHA1)

## Security Features

**Time-based** - Codes expire automatically
**Stateless** - No server-side storage of codes
**Standardized** - RFC 6238 compliant
**Clock drift tolerant** - ±30 second window
**Offline capable** - Works without internet
**Reset capable** - Full user management
**Status tracking** - Monitor 2FA state

## Enhanced Educational Value

### Students Will Learn:
1. **TOTP Algorithm** - How time-based codes are generated
2. **Cryptographic Hashing** - HMAC-SHA1 usage
3. **Base32 Encoding** - QR code data format
4. **API Security** - Multi-factor authentication flow
5. **User Management** - Account state and reset procedures
6. **Dependency Management** - Handling deprecated services
7. **Error Handling** - Graceful failure and recovery
8. **Real-world Implementation** - Industry-standard 2FA

### Key Concepts:
- **Something you know** (password) + **Something you have** (phone)
- **Shared secret** cryptography
- **Time synchronization** importance
- **Attack prevention** (replay, brute force)
- **Account recovery** procedures
- **Service reliability** and fallback strategies

## Enhanced Testing Scenarios

### **Valid Reset Flow:**
```php
// 1. Check status
GET /status.php → Show current state

// 2. Reset 2FA
POST /reset.php → Disable 2FA

// 3. Setup new 2FA
POST /setup.php → New QR Code

// 4. Verify setup
POST /verify.php → Enable 2FA

// 5. Login with new 2FA
POST /login.php → Success
```

### **Error Cases:**
- User not found → `User not found`
- Wrong password → `Invalid credentials`
- 2FA not enabled → `2FA is not enabled for this user`
- Reset without confirmation → `Reset confirmation required`
- Invalid TOTP code → `Invalid 2FA code`
- Expired code → `Invalid 2FA code`
- Clock drift → Still works (±30s tolerance)

### **QR Code Fallback:**
- Primary: QR Server API (api.qrserver.com)
- Backup: QuickChart (quickchart.io)
- Manual: Base32 key entry
- Testing: Multiple service validation

## Production Notes

**This is educational code. For production:**
- Use `firebase/php-jwt` or similar library
- Store secrets encrypted in database
- Implement rate limiting
- Use HTTPS only
- Add backup codes
- Hash passwords with `password_hash()`
- Implement CSRF protection
- Add audit logging
- Require email verification for resets
- Implement admin approval workflows
- Use proper session management

## Assignment Ideas

1. **Enhanced backup codes** - Generate one-time recovery codes
2. **Rate limiting** - Prevent brute force attacks on reset endpoints
3. **Admin panel** - UI for managing user 2FA states
4. **SMS fallback** - Alternative to TOTP with Twilio
5. **Email verification** - Require email confirmation for resets
6. **Audit logging** - Track all 2FA events and changes
7. **Mobile responsive** - Better phone UI
8. **Multi-service QR** - Compare different QR code providers
9. **Recovery workflows** - More sophisticated reset procedures
10. **Integration testing** - Automated API testing suite

## Troubleshooting Guide

### **QR Code Not Showing:**
1. Check if Google Charts API is being used (deprecated)
2. Run `php test/test.php` and verify the generated manual-entry key
3. Try backup QR service in web interface
4. Use manual Base32 key entry

### **2FA Already Enabled Error:**
1. Use web interface reset feature
2. Run: `php quick_reset.php username`
3. Use force reset: `{"force_reset": true}`
4. Check status: `curl "status.php?username=john"`

### **Codes Not Working:**
1. Check time synchronization
2. Verify ±30 second tolerance
3. Ensure correct secret is used
4. Test with multiple time windows

### **Reset Not Working:**
1. Verify user credentials
2. Add `"confirm_reset": true`
3. Check file permissions on users_data.json
4. Use command-line tool for debugging

## References

- **RFC 6238**: TOTP Algorithm
- **RFC 4226**: HOTP Algorithm  
- **Google Authenticator**: Open source implementation
- **OWASP 2FA**: Security guidelines
- **QR Server API**: Modern QR code generation
- **QuickChart**: QR code service alternative

---

**Enhanced for educational excellence with complete reset functionality!**
