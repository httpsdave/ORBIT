# Google Drive Backup Setup Instructions

## Backup system successfully configured! ✓

Your Laravel application is now set up to automatically backup all submissions and database data to Google Drive (lspuorbit@gmail.com) daily at 3:30 AM.

## What's been configured:

1. ✓ Spatie Laravel Backup package installed
2. ✓ Google Drive filesystem adapter configured
3. ✓ Backup configuration created
4. ✓ Automatic daily backup schedule set up

## Next Steps - Get Google Drive Credentials:

### 1. Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Sign in with **lspuorbit@gmail.com**
3. Create a new project or select an existing one
4. Enable the **Google Drive API** for your project

### 2. Create OAuth 2.0 Credentials

1. Go to **APIs & Services** → **Credentials**
2. Click **Create Credentials** → **OAuth client ID**
3. Select **Application type: Web application**
4. Add authorized redirect URI: `http://localhost` (for getting refresh token)
5. Click **Create** and save your **Client ID** and **Client Secret**

### 3. Get Refresh Token

Run this PHP script to get your refresh token:

```php
<?php
// google-drive-token.php
require 'vendor/autoload.php';

$client = new \Google_Client();
$client->setClientId('YOUR_CLIENT_ID');
$client->setClientSecret('YOUR_CLIENT_SECRET');
$client->setRedirectUri('http://localhost');
$client->addScope(\Google_Service_Drive::DRIVE);
$client->setAccessType('offline');
$client->setPrompt('consent');

if (!isset($_GET['code'])) {
    $authUrl = $client->createAuthUrl();
    echo "Visit this URL and authorize the app:\n\n";
    echo $authUrl . "\n\n";
    echo "After authorizing, you'll be redirected. Copy the 'code' parameter from the URL and run:\n";
    echo "php google-drive-token.php?code=YOUR_CODE\n";
} else {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    echo "Refresh Token: " . $token['refresh_token'] . "\n";
}
```

Run: `php google-drive-token.php`

### 4. Add Credentials to .env

Add these to your `.env` file (both local and Railway):

```env
GOOGLE_DRIVE_CLIENT_ID=your_client_id_here
GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret_here
GOOGLE_DRIVE_REFRESH_TOKEN=your_refresh_token_here
GOOGLE_DRIVE_FOLDER=/Backups/LSPU-Orbit

# Optional: Get backup notifications via email
BACKUP_NOTIFICATION_EMAIL=lspuorbit@gmail.com
```

### 5. Create Backup Folder on Google Drive

1. Log into Google Drive with lspuorbit@gmail.com
2. Create a folder: **Backups/LSPU-Orbit**
3. This is where all backups will be stored

### 6. Test the Backup

Run manually to test:

```bash
php artisan backup:run
```

You should see the backup files appear in your Google Drive folder!

### 7. Set up Scheduler on Railway

For automatic backups on Railway, you need to ensure the Laravel scheduler runs. Add this to your Railway service:

**Option A: Add to startup.sh or Procfile**
```bash
* * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1
```

**Option B: Use Railway Cron (if available)**
Create a separate cron service that runs:
```bash
php artisan schedule:run
```

## Manual Backup Commands

- **Run backup now:** `php artisan backup:run`
- **Clean old backups:** `php artisan backup:clean`
- **Check backup health:** `php artisan backup:list`
- **Backup only database:** `php artisan backup:run --only-db`

## Backup Schedule

- **3:00 AM:** Clean old backups (keeps last 7 days daily, 16 days with one per day, etc.)
- **3:30 AM:** Create new full backup (database + files)

## What Gets Backed Up

- **Database:** All MySQL tables (submissions, users, colleges, etc.)
- **Files:** Application files (excluding vendor/ and node_modules/)
- **Retention:** Keeps backups for 7 days (daily), 16 days, 8 weeks, 4 months, 2 years

## Storage Limit

- Max backup storage: 5GB
- Oldest backups automatically deleted when limit reached

## Troubleshooting

### "Unable to create a directory"
Make sure `storage/app/backup-temp` exists and is writable:
```bash
mkdir -p storage/app/backup-temp
chmod -R 775 storage/app/backup-temp
```

### "Invalid credentials"
Double-check your Google Drive credentials in .env

### "Backup failed"
Check logs: `storage/logs/laravel.log`

### Test connection
```bash
php artisan tinker
Storage::disk('google')->put('test.txt', 'Hello Google Drive!');
```

## Important Notes

- Backups are compressed and stored as `.zip` files
- Each backup includes a timestamp in the filename
- Email notifications will be sent to lspuorbit@gmail.com for backup status
- Make sure your Railway service has enough memory for database dumps
- For large databases, consider using `--only-db` flag for faster backups

## Alternative: Manual mysqldump Script

If automated backups don't work on Railway, use this manual script:

```bash
#!/bin/bash
# manual-backup.sh
mysqldump -h $MYSQL_HOST -P $MYSQL_PORT -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DB | gzip > backup_$(date +%Y%m%d).sql.gz
```

Run weekly via local cron or GitHub Actions.
