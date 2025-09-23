# Complete File Upload Audit - Railway Volumes Compatibility

## ✅ Already Compatible - Using 'public' Disk

### Organization Application Files
**Location**: `OrganizationApplicationController.php`
**Storage Path**: `storage/app/public/`

1. **LSPU-OSAS-SF-BYLAWS** (Your focus!)
   - Line 1762: `$path = $file->storeAs('reports/' . $user->id, $fileName, 'public');`
   - Column: `bylaws_path`
   - Path: `reports/{user_id}/LSPU-OSAS-SF-BYLAWS_{timestamp}.pdf`

2. **Other Report Types**:
   - LSPU-OSAS-SF-ACCOMPLISHMENT → `accomplishment_report_path`
   - LSPU-OSAS-SF-NARRATIVE → `narrative_report_path`
   - LSPU-OSAS-SF-FINANCIAL → `financial_report_path`
   - LSPU-ACAD-RL → `event_letter_path`

3. **Signed Documents**:
   - Line 921: `$path = $request->file('signed_document')->store('signed_documents', 'public');`
   - Path: `signed_documents/{filename}`

4. **Member Photos**:
   - Line 354: `$path = $member['photo_path']->store('member_photos', 'public');`
   - Path: `member_photos/{filename}`

5. **Officer Photos**:
   - Line 366: `$path = $officer['photo_path']->store('officer_photos', 'public');`
   - Path: `officer_photos/{filename}`

### College Management
**Location**: `Admin/CollegeController.php`
- Line 56: `$logoPath = $request->file('logo')->store('college-logos', 'public');`
- Path: `college-logos/{filename}`

### Event Documents
**Location**: `EventController.php`
- Line 156: `$filePath = $file->storeAs('documents', $fileName, 'public');`
- Path: `documents/{filename}`

## ⚠️  Needs Attention - Using 'local' Disk

### Activity Reports
**Location**: `OrganizationApplicationController.php`
**Current**: Lines 2095, 2108 - Using `Storage::disk('local')`
**Issue**: These files won't persist across deployments

**Fix Required**: Change `local` to `public` disk

## Summary
- **18+ upload locations** already use `public` disk ✅
- **2 locations** use `local` disk and need fixing ⚠️
- **Railway Volume** will make ALL `public` disk files persistent

## Files That Will Persist After Volume Setup
✅ Bylaws submissions (LSPU-OSAS-SF-BYLAWS)
✅ All other form submissions
✅ Signed documents
✅ Member & officer photos
✅ College logos
✅ Event documents
❌ Activity reports (need disk change from 'local' to 'public')