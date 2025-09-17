# Firebase Password Reset Setup for Railway Deployment

## Overview
This guide will help you set up Firebase Authentication for password reset functionality only, while maintaining Laravel's existing authentication system. The setup is specifically configured for Railway deployment.

## Step 1: Create Firebase Project

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click "Create a project" or "Add project"
3. Enter project name: `lspu-osas-password-reset`
4. **Important**: Disable Google Analytics (not needed for password reset)
5. Click "Create project"

## Step 2: Enable Authentication

1. In your Firebase project, click on "Authentication" in the left sidebar
2. Click "Get started"
3. Go to "Sign-in method" tab
4. Enable "Email/Password" provider:
   - Toggle "Enable" switch
   - **DO NOT** enable "Email link (passwordless sign-in)"
   - Click "Save"

## Step 3: Configure Authorized Domains

1. Still in "Sign-in method" tab, scroll down to "Authorized domains"
2. Add your Railway domain:
   - Click "Add domain"
   - Enter: `your-app-name.up.railway.app` (replace with your actual Railway domain)
   - Click "Add"
3. Keep `localhost` for local development

## Step 4: Get Firebase Configuration

1. Click on "Project settings" (gear icon next to "Project Overview")
2. Scroll down to "Your apps" section
3. Click on "Web" icon (`</>`)
4. Register app with nickname: `LSPU-OSAS-Web`
5. **DO NOT** set up Firebase Hosting
6. Copy the Firebase configuration object (it looks like this):

```javascript
const firebaseConfig = {
  apiKey: "your-api-key-here",
  authDomain: "your-project-id.firebaseapp.com",
  projectId: "your-project-id",
  storageBucket: "your-project-id.appspot.com",
  messagingSenderId: "123456789",
  appId: "your-app-id"
};
```

## Step 5: Update Firebase Configuration File

1. Open `resources/js/firebase/config.js` in your project
2. Replace the placeholder configuration with your actual Firebase config:

```javascript
import { initializeApp } from 'firebase/app';
import { getAuth } from 'firebase/auth';

const firebaseConfig = {
  apiKey: "your-actual-api-key",
  authDomain: "your-project-id.firebaseapp.com",
  projectId: "your-project-id",
  storageBucket: "your-project-id.appspot.com",
  messagingSenderId: "your-actual-sender-id",
  appId: "your-actual-app-id"
};

const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
```

## Step 6: Railway Environment Variables

1. Go to your Railway dashboard
2. Select your project
3. Go to "Variables" tab
4. Add these environment variables (if not already present):

```
APP_URL=https://your-app-name.up.railway.app
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Step 7: Configure Custom Email Action Handler (Optional)

If you want to use custom email templates or redirect users to your application:

1. In Firebase Console, go to "Authentication" > "Templates"
2. Click on "Password reset" template
3. Customize the email template if needed
4. Set the action URL to: `https://your-app-name.up.railway.app/reset-password`

## Step 8: Test the Integration

### Local Testing:
1. Run your Laravel application locally
2. Go to `/forgot-password`
3. Enter a valid user email address
4. Check that the email is sent and contains the reset link
5. Click the reset link and verify the password reset works

### Railway Testing:
1. Deploy your application to Railway
2. Test the same flow on your live domain
3. Verify emails are sent and reset links work correctly

## Step 9: Monitor and Maintain

### Firebase Usage Monitoring:
1. Go to Firebase Console > "Usage" tab
2. Monitor your Authentication usage (free tier includes 10K verifications/month)

### Error Monitoring:
1. Check Railway logs for any Firebase-related errors
2. Monitor Laravel logs in `storage/logs/laravel.log`

## Security Considerations

1. **API Key Security**: Firebase API keys are safe to use in client-side code but ensure they're properly configured
2. **Domain Restrictions**: Only add necessary domains to authorized domains list
3. **User Verification**: The system checks if users exist in Laravel database before sending reset emails
4. **Rate Limiting**: Firebase has built-in rate limiting for password reset requests

## Troubleshooting Common Issues

### Issue: "Invalid domain" error
**Solution**: Ensure your Railway domain is added to Firebase authorized domains

### Issue: Password reset emails not sending
**Solution**: 
1. Check if Email/Password provider is enabled in Firebase
2. Verify SMTP settings in Railway environment variables
3. Check Firebase project quota

### Issue: Reset link shows "Invalid or expired"
**Solution****: 
1. Links expire after 1 hour by default
2. Each link can only be used once
3. Request a new reset if needed

### Issue: CORS errors in browser
**Solution**: Ensure Firebase configuration is correct and domains are authorized

## Additional Notes

- Firebase password reset emails are sent from Firebase's infrastructure, not your SMTP server
- The integration maintains Laravel's user database - only password reset emails go through Firebase
- Users don't need Firebase accounts - the system uses their existing Laravel accounts
- All user sessions and authentication (except password reset) remain handled by Laravel

## Support

If you encounter issues:
1. Check Firebase Console for error logs
2. Review Railway deployment logs
3. Verify all environment variables are set correctly
4. Ensure Firebase configuration matches your project settings