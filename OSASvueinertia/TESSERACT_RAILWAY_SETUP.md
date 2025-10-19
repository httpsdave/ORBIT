# TesseractOCR Setup for Railway Deployment

## ✅ Changes Made

### 1. Updated `nixpacks.toml`
Added `tesseract` to the nixPkgs array to install Tesseract OCR on Railway:

```toml
nixPkgs = ['...', 'nodejs_18', 'tesseract']
```

### 2. Enhanced `EventController.php`
Improved Tesseract configuration for better accuracy:

```php
$ocr = new TesseractOCR(storage_path('app/public/' . $filePath));
$ocr->lang('eng')  // English language
    ->psm(3)       // Automatic page segmentation
    ->oem(1);      // Neural nets LSTM engine
```

## 🚀 Deployment Steps

### Step 1: Commit and Push Changes
```bash
git add nixpacks.toml
git add app/Http/Controllers/EventController.php
git commit -m "feat: Add Tesseract OCR to Railway deployment"
git push origin main
```

### Step 2: Railway Will Automatically Deploy
- Railway detects the `nixpacks.toml` changes
- Tesseract will be installed during the setup phase
- Your application will use Tesseract as the primary OCR method

### Step 3: Verify Deployment
After deployment completes, check Railway logs:
```bash
railway logs --tail
```

Look for successful Tesseract installation messages.

## 📊 How It Works Now

### Processing Flow:
1. **User uploads image** (JPEG, PNG) up to 10MB
2. **TesseractOCR processes it** (FREE, no limits)
3. **If Tesseract fails** → Falls back to OCR.Space (with compression for files >1MB)
4. **Extract event info** using Gemini AI
5. **Return structured data**

### Benefits:
- ✅ **No file size limits** - Tesseract handles 10MB images
- ✅ **FREE** - No API costs
- ✅ **Faster** - Local processing
- ✅ **Private** - No external API calls
- ✅ **Reliable fallback** - OCR.Space as backup with compression

## 🔍 Testing After Deployment

### Test TesseractOCR:
1. Go to your Calendar page
2. Upload an event document (image)
3. Check the response in browser console:
```json
{
  "ocr_method": "tesseract",  // Should show "tesseract"
  "raw_text": "...",
  "title": "...",
  ...
}
```

If `ocr_method` is `"tesseract"`, it's working! 🎉

## 🛠️ Troubleshooting

### If Tesseract Fails on Railway:

#### Check 1: Verify Tesseract Installation
Add to Railway startup or check logs:
```bash
which tesseract
tesseract --version
```

#### Check 2: Install Additional Language Data (if needed)
Update `nixpacks.toml`:
```toml
nixPkgs = ['...', 'nodejs_18', 'tesseract', 'tesseract4.tessdata']
```

#### Check 3: Check File Permissions
Ensure storage directory is writable:
```bash
railway run php artisan storage:link
chmod -R 775 storage
```

## 🎯 Alternative: Remove OCR.Space Fallback (Optional)

If you want to use **ONLY** TesseractOCR and remove OCR.Space dependency:

### Update EventController.php:
```php
if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
    try {
        $ocr = new TesseractOCR(storage_path('app/public/' . $filePath));
        $ocr->lang('eng')->psm(3)->oem(1);
        $text = $ocr->run();
        
        if (empty(trim($text))) {
            throw new \Exception("No text extracted from image");
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to extract text from image using Tesseract OCR.',
            'details' => $e->getMessage()
        ], 500);
    }
}
```

## 📈 Performance Comparison

| Method | Speed | Cost | File Size Limit | Accuracy |
|--------|-------|------|-----------------|----------|
| **TesseractOCR** | ~2-5s | FREE | No limit | 80-85% |
| OCR.Space (fallback) | ~3-7s | FREE tier | 1MB | 85-90% |
| Google Vision API | ~1-3s | $1.50/1000 | 20MB | 95%+ |

## 🌟 Recommendations

### For Your Use Case (Event Documents):
1. ✅ **Primary: TesseractOCR** - FREE, no limits, good for clean documents
2. ✅ **Fallback: OCR.Space** - Handles complex layouts better
3. ✅ **AI Enhancement: Gemini** - Structures the extracted text

### Future Enhancements (Optional):
- **Phase 1**: Current setup (Tesseract + OCR.Space fallback)
- **Phase 2**: Add Google Cloud Vision API for highest accuracy
- **Phase 3**: Train custom ML model for event-specific extraction

## ✨ Summary

Your deployment is now configured to:
1. Use **TesseractOCR by default** (FREE, no limits)
2. Fall back to **OCR.Space with compression** if needed
3. Process with **Gemini AI** for intelligent extraction

This gives you the best of both worlds: FREE primary OCR with a reliable paid fallback! 🚀
