# Pwned Passwords by FriendsOfFlarum

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/fof/pwned-passwords.svg)](https://packagist.org/packages/fof/pwned-passwords) [![OpenCollective](https://img.shields.io/badge/opencollective-fof-blue.svg)](https://opencollective.com/fof/donate)

Protects your Flarum community by checking passwords against [Have I Been Pwned](https://haveibeenpwned.com/Passwords) — a database of passwords exposed in known data breaches. Passwords are checked securely using the k-anonymity model: only the first 5 characters of the SHA-1 hash are ever sent to the API, so no plaintext password data leaves your server.

## Features

- **Registration check** — blocks sign-up with a known-compromised password
- **Password reset check** — prevents users from resetting to a known-compromised password
- **Login check** *(optional)* — detects accounts already using a compromised password at login time and sends a password reset email automatically
- **Admin revocation** *(optional)* — strips admin permissions from any account using a compromised password until it is changed
- **Persistent notice banner** — shows analert to the affected user on every page until they change their password, with a "Resend Reset Email" button and a configurable "Learn More" link
- **Configurable learn-more URL** — defaults to `haveibeenpwned.com/Passwords`; can be overridden in the admin panel with a forum-hosted explanation page

## How it works

Password checks use the [HIBP Pwned Passwords range API](https://haveibeenpwned.com/API/v3#PwnedPasswords) with k-anonymity:

1. The password is hashed with SHA-1 locally
2. Only the first 5 hex characters of the hash are sent to `api.pwnedpasswords.com`
3. The API returns all matching hash suffixes (padded to a consistent size)
4. The extension checks whether the full hash appears in the results — entirely client-side (server-side in PHP)

No password or full hash is ever transmitted.

## Installation

```bash
composer require fof/pwned-passwords
```

## Updating

```bash
composer update fof/pwned-passwords
php flarum migrate
php flarum cache:clear
```

## Configuration

Navigate to **Admin → Extensions → FoF Pwned Passwords**:

| Setting | Description |
|---|---|
| Enable password check on login | Check passwords at login and send a reset email if compromised |
| Revoke permissions from pwned admins | Remove admin access until the user changes their password |
| "Learn More" link URL | URL shown in the notice banner (defaults to `haveibeenpwned.com/Passwords`) |

## Links

[![Donate](https://img.shields.io/badge/donate-friendsofflarum-44AEE5?style=for-the-badge&logo=open-collective)](https://opencollective.com/fof/donate)

- [Packagist](https://packagist.org/packages/fof/pwned-passwords)
- [GitHub](https://github.com/FriendsOfFlarum/pwned-passwords)
- [Discuss](https://discuss.flarum.org/d/18348)

An extension by [FriendsOfFlarum](https://github.com/FriendsOfFlarum).
