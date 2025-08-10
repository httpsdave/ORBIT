# Railway Deployment Guide for ORBIT Laravel Application

## Prerequisites
- Railway account (https://railway.app)
- GitHub repository with your code
- Git installed on your local machine

## Files Created for Deployment
- `nixpacks.toml` - Railway build configuration
- `Procfile` - Process definitions for Railway
- `deploy.sh` - Deployment script for migrations and setup
- `.env.railway` - Environment variables template for Railway

## Deployment Steps

### 1. Push Code to GitHub
Make sure your code is pushed to a GitHub repository that Railway can access.

### 2. Create Railway Project
1. Go to https://railway.app and sign in
2. Click "New Project"
3. Select "Deploy from GitHub repo"
4. Choose your repository

### 3. Add MySQL Database
1. In your Railway project dashboard, click "New"
2. Select "Database" → "MySQL"
3. Railway will automatically create a MySQL database

### 4. Configure Environment Variables
In your Railway project settings, add these environment variables:

**Required Variables (copy from .env.railway file):**
- APP_NAME=ORBIT
- APP_ENV=production
- APP_DEBUG=false
- APP_URL=https://your-actual-railway-url.railway.app
- APP_KEY=base64:lKOb8waV8yMqY+11A32gzreeBPumUjn+oJvIbWAGU1c=

**Database Variables (Railway auto-provides these):**
- DB_CONNECTION=mysql
- DB_HOST=${{MYSQLHOST}}
- DB_PORT=${{MYSQLPORT}}
- DB_DATABASE=${{MYSQLDATABASE}}
- DB_USERNAME=${{MYSQLUSER}}
- DB_PASSWORD=${{MYSQLPASSWORD}}

**Other Required Variables:**
- MAIL_USERNAME=davedominic911@gmail.com
- MAIL_PASSWORD="nlxl xnef atft zjpy"
- HUGGINGFACE_API_KEY=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

### 5. Deploy
1. Railway will automatically deploy when you push to your main branch
2. The deploy.sh script will run migrations and seeders
3. Your app will be available at the Railway-provided URL

## Important Notes

### Database Seeding
- The deployment script automatically runs `php artisan db:seed --force`
- This will create:
  - Admin user (email: admin@example.com, password: password)
  - 7 test users
  - College data
  - Role data
  - Welcome notifications

### File Storage
- Railway provides ephemeral storage
- Uploaded files will be lost on redeploy
- Consider using AWS S3 or similar for persistent file storage

### Environment Variables
- Never commit sensitive data to your repository
- Use Railway's environment variable system
- Update APP_URL to match your Railway domain

## Troubleshooting

### Build Failures
- Check Railway logs for specific error messages
- Ensure all dependencies are in composer.json
- Verify PHP version compatibility (requires PHP 8.2+)

### Database Connection Issues
- Verify database environment variables are set correctly
- Check that MySQL service is running in Railway
- Ensure database migrations complete successfully

### Asset Issues
- Make sure `npm run build` completes successfully
- Verify Vite builds assets correctly
- Check that public/build directory is created
