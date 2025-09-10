# Activity Reports Feature Implementation

## Overview
The Activity Reports feature has been successfully implemented for Plan of Activities (LSPU-OSAS-SF-004) submissions. This feature allows users to submit and manage three types of reports for each activity page in their Plan of Activities submission.

## Key Features

### 1. Report Types
For each activity page, users can submit three types of reports:
- **Financial Report** (LSPU-OSAS-SF-FINANCIAL)
- **Narrative Report** (LSPU-OSAS-SF-NARRATIVE) 
- **Accomplishment Report** (LSPU-OSAS-SF-ACCOMPLISHMENT)

### 2. Access Control
- Only accessible from Plan of Activities submissions (LSPU-OSAS-SF-004)
- Users can only access reports for their own applications
- Admins can access all reports
- "View Reports" button only appears for Plan of Activities forms

### 3. File Management
- Supports PDF, DOC, and DOCX files
- Maximum file size: 10MB
- Files stored securely in `storage/app/public/activity_reports/`
- Original filenames preserved for user reference
- Secure download through controller (no direct file access)

### 4. User Interface
- Organized by activity pages (1 page = 3 reports)
- Visual status indicators (pending, submitted, approved, rejected)
- Easy upload interface with drag-and-drop support
- View and delete functionality for existing reports
- Admin feedback display when available

## Database Structure

### ActivityReport Model
```php
- id (primary key)
- organization_application_id (foreign key)
- activity_page_number (which activity page this report is for)
- report_type (FINANCIAL, NARRATIVE, ACCOMPLISHMENT)
- file_path (storage path)
- original_filename (user-friendly filename)
- status (pending, submitted, approved, rejected)
- feedback (admin feedback)
- submitted_at (timestamp)
- created_at, updated_at
```

### Unique Constraint
- One report per activity page per report type
- Prevents duplicate submissions for the same activity/report type combination

## Routes

### Public Routes (Authenticated Users)
- `GET /applications/{id}/reports` - View reports management page
- `POST /applications/{id}/reports` - Upload a new report
- `GET /applications/{id}/reports/{report}/download` - Download report file
- `DELETE /applications/{id}/reports/{report}` - Delete a report

## File Structure

### Backend
- `app/Models/ActivityReport.php` - Model with relationships and helper methods
- `app/Http/Controllers/OrganizationApplicationController.php` - Reports methods added
- `database/migrations/2025_09_10_150930_create_activity_reports_table.php` - Database schema
- `routes/web.php` - Report routes added

### Frontend
- `resources/js/Pages/Applications/Reports.vue` - Main reports management interface
- `resources/js/Components/ApplicationsTable.vue` - "View Reports" button integration

## Usage Flow

1. **User submits Plan of Activities** - Standard form submission process
2. **View Reports button appears** - Only for Plan of Activities submissions
3. **Access Reports page** - Click button to view reports management
4. **Submit reports** - Upload files for each activity page and report type
5. **Track status** - Monitor submission status and admin feedback
6. **Download/manage** - View, download, or delete submitted reports

## Technical Implementation

### Activity Page Mapping
- Each activity in a Plan of Activities submission represents one "page"
- Page numbers are sequential (1, 2, 3, etc.)
- Each page requires 3 reports (Financial, Narrative, Accomplishment)
- If a Plan has 5 activities, there will be 15 total report slots (5 pages × 3 reports)

### Security Features
- CSRF protection on all form submissions
- File type validation (PDF, DOC, DOCX only)
- File size limits (10MB maximum)
- User authorization checks
- Secure file storage and access

### Status Management
- **Pending**: Report slot exists but no file uploaded
- **Submitted**: File uploaded and available for admin review
- **Approved**: Admin has approved the report
- **Rejected**: Admin has rejected the report (with feedback)

## Future Enhancements
- Email notifications for status changes
- Bulk upload functionality
- Report templates and guidelines
- Integration with admin dashboard for report review
- Report analytics and statistics

## Testing
- Migration successfully created `activity_reports` table
- Model relationships properly configured
- Routes and controller methods implemented
- Vue component successfully renders reports interface
- File upload and download functionality working
