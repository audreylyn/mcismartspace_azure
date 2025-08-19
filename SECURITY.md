# Security Features Documentation

This document outlines the security features implemented in MCiSmartSpace Room Management System to protect user data and prevent unauthorized access.

## Authentication Security

### Rate Limiting

- **Maximum Attempts**: 5 login attempts allowed per IP address
- **Lockout Period**: 15-minute lockout after exceeding the maximum attempts
- **IP-based Tracking**: System tracks login attempts by IP address
- **Automatic Cleanup**: Old login attempt records are automatically removed after 24 hours
- **Visual Indicators**: Users receive warnings showing remaining login attempts

### Password Security

- **Hashed Storage**: All user passwords (except Registrar) are stored using PHP's password_hash()
- **Secure Verification**: Passwords are verified using password_verify() to prevent timing attacks
- **Input Validation**: All login credentials are validated before processing

### Brute Force Protection

- **Progressive Delays**: Automatic account lockout after multiple failed attempts
- **Failed Attempt Logging**: All failed login attempts are recorded in the database
- **Secure Login Redirects**: Users are redirected based on their role without manual selection

## Session Management

### Session Security

- **Timeout Period**: 30-minute session timeout (configurable)
- **Session Regeneration**: Automatic session ID regeneration every 5 minutes
- **IP Address Validation**: Sessions are validated against the original IP address to prevent session hijacking
- **User Agent Validation**: Sessions are bound to the user's browser to add an additional security layer
- **Timeout Warnings**: Users receive warnings 5 minutes before their session expires

### Cookie Security

- **HTTP-Only Flag**: Session cookies are set with the HttpOnly flag to prevent JavaScript access
- **Secure Flag**: Cookies are set with the Secure flag when HTTPS is detected
- **SameSite Policy**: Cookies use SameSite=Strict to prevent CSRF attacks
- **Expiration Control**: Session cookies expire after the configured timeout period

## Data Protection

### Input/Output Security

- **SQL Injection Prevention**: Prepared statements used for all database queries
- **XSS Prevention**: User inputs are sanitized before being rendered in HTML
- **CSRF Protection**: Forms use tokens to prevent cross-site request forgery

### Access Control

- **Role-Based Access**: Different user roles (Registrar, Department Admin, Teacher, Student) have specific access permissions
- **Authorization Checks**: Each page verifies that the user has appropriate access rights
- **Automatic Redirection**: Unauthorized access attempts are redirected to the login page

## System Security

### Database Security

- **Parameterized Queries**: All database interactions use prepared statements with bound parameters
- **Error Handling**: Database errors are logged without exposing sensitive information to users
- **Secure Connection**: Database credentials are not exposed in client-side code

### Application Structure

- **Separation of Concerns**: Authentication logic is separated from business logic
- **Middleware Pattern**: Security checks are implemented as middleware
- **Centralized Configuration**: Security settings are defined in central configuration files

## Testing Security Features

To test these security features:

### Rate Limiting Test

1. Attempt to log in with incorrect credentials 5 times
2. On the 6th attempt, you should be locked out for 15 minutes
3. The lockout applies to the IP address, regardless of the account being accessed

### Session Security Test

1. Log in to your account
2. Leave the browser idle for 25 minutes
3. You should receive a warning about session expiration
4. After 30 minutes of inactivity, you will be automatically logged out

### Role-Based Access Test

1. Log in as a Student
2. Attempt to access Department Admin pages by modifying the URL
3. You should be redirected to the login page with an "Access denied" message

