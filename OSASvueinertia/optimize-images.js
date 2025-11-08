/**
 * Image Optimization Script
 * 
 * This script optimizes images for better LCP performance
 * Run with: node optimize-images.js
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

console.log('Image Optimization Recommendations');
console.log('====================================\n');

console.log('To optimize your LSPU images for better LCP performance:');
console.log('');
console.log('1. RESIZE IMAGES:');
console.log('   - Current: 1920x1080 (full HD)');
console.log('   - Recommended for web: 1280x720 or 1600x900');
console.log('   - This will reduce file size by 40-60%');
console.log('');
console.log('2. COMPRESS IMAGES:');
console.log('   - Use online tools:');
console.log('     • TinyPNG (https://tinypng.com)');
console.log('     • Squoosh (https://squoosh.app)');
console.log('     • ImageOptim (Mac)');
console.log('   - Target: 80-85% quality for JPEG');
console.log('');
console.log('3. CONVERT TO WEBP:');
console.log('   - WebP provides 25-35% better compression');
console.log('   - Use with JPEG fallback for older browsers');
console.log('');
console.log('4. USE RESPONSIVE IMAGES:');
console.log('   - Create multiple sizes: 640w, 1024w, 1600w');
console.log('   - Use srcset attribute for different screen sizes');
console.log('');
console.log('5. INSTALL SHARP (for automated optimization):');
console.log('   npm install -D sharp');
console.log('   Then run: node optimize-images.js --process');
console.log('');

// Check if sharp is installed
try {
    await import('sharp');
    console.log('✓ Sharp is installed. You can use --process flag to optimize images.');
    
    if (process.argv.includes('--process')) {
        await optimizeImages();
    }
} catch (e) {
    console.log('✗ Sharp not installed. Run: npm install -D sharp');
    console.log('  Then run this script again with --process flag');
}

async function optimizeImages() {
    const sharp = (await import('sharp')).default;
    const imagesDir = path.join(__dirname, 'public', 'images');
    const outputDir = path.join(__dirname, 'public', 'images', 'optimized');
    
    // Create output directory if it doesn't exist
    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
    }
    
    console.log('\nOptimizing images...\n');
    
    const imageFiles = [
        'LSPU9.jpg',
        'LSPU2.jpg',
        'LSPU3.jpg',
        'LSPU6.jpg',
        'LSPU5.jpg',
        'LSPU7.jpg'
    ];
    
    for (const file of imageFiles) {
        const inputPath = path.join(imagesDir, file);
        
        if (!fs.existsSync(inputPath)) {
            console.log(`✗ ${file} not found, skipping...`);
            continue;
        }
        
        const baseName = path.basename(file, path.extname(file));
        
        try {
            // Get original file size
            const originalStats = fs.statSync(inputPath);
            const originalSize = (originalStats.size / 1024).toFixed(2);
            
            // Optimize JPEG
            const jpegPath = path.join(outputDir, `${baseName}.jpg`);
            await sharp(inputPath)
                .resize(1600, 900, { 
                    fit: 'cover',
                    position: 'center'
                })
                .jpeg({ 
                    quality: 82,
                    progressive: true,
                    mozjpeg: true
                })
                .toFile(jpegPath);
            
            const jpegStats = fs.statSync(jpegPath);
            const jpegSize = (jpegStats.size / 1024).toFixed(2);
            const jpegSavings = ((1 - jpegStats.size / originalStats.size) * 100).toFixed(1);
            
            // Create WebP version
            const webpPath = path.join(outputDir, `${baseName}.webp`);
            await sharp(inputPath)
                .resize(1600, 900, { 
                    fit: 'cover',
                    position: 'center'
                })
                .webp({ 
                    quality: 82,
                    effort: 6
                })
                .toFile(webpPath);
            
            const webpStats = fs.statSync(webpPath);
            const webpSize = (webpStats.size / 1024).toFixed(2);
            const webpSavings = ((1 - webpStats.size / originalStats.size) * 100).toFixed(1);
            
            // Create mobile version (smaller)
            const mobilePath = path.join(outputDir, `${baseName}-mobile.jpg`);
            await sharp(inputPath)
                .resize(1024, 576, { 
                    fit: 'cover',
                    position: 'center'
                })
                .jpeg({ 
                    quality: 80,
                    progressive: true,
                    mozjpeg: true
                })
                .toFile(mobilePath);
            
            const mobileStats = fs.statSync(mobilePath);
            const mobileSize = (mobileStats.size / 1024).toFixed(2);
            
            console.log(`✓ ${file}:`);
            console.log(`  Original:      ${originalSize} KB`);
            console.log(`  Optimized JPG: ${jpegSize} KB (${jpegSavings}% smaller)`);
            console.log(`  WebP:          ${webpSize} KB (${webpSavings}% smaller)`);
            console.log(`  Mobile JPG:    ${mobileSize} KB`);
            console.log('');
            
        } catch (error) {
            console.error(`✗ Error optimizing ${file}:`, error.message);
        }
    }
    
    console.log('\n✓ Optimization complete!');
    console.log(`\nOptimized images saved to: ${outputDir}`);
    console.log('\nNext steps:');
    console.log('1. Review the optimized images');
    console.log('2. Replace original images in public/images/');
    console.log('3. Update Logz.vue to use WebP with JPEG fallback');
    console.log('4. Run npm run build');
}
