# Privacy Policy Access Instructions

## How to Access the Privacy Policy

### 1. Local Testing
You can open the privacy policy HTML file directly in your web browser:

```bash
# Method 1: Double-click the file
Double-click on `privacy-policy.html` in your file explorer

# Method 2: Use browser's open file feature
- Open your web browser (Chrome, Firefox, Safari, etc.)
- Press `Ctrl+O` (Windows/Linux) or `Cmd+O` (Mac)
- Navigate to the project directory
- Select `privacy-policy.html`

# Method 3: Command line (if you have a web server)
cd /home/smith/Desktop/MAN/manchoice-backend
python3 -m http.server 8000
# Then visit: http://localhost:8000/privacy-policy.html
```

### 2. Hosting for Production

#### Option A: Upload to Web Hosting Service
1. Upload `privacy-policy.html` to your web hosting service
2. Access via: `https://yourdomain.com/privacy-policy.html`

#### Option B: GitHub Pages (Free)
1. Create a GitHub repository
2. Upload `privacy-policy.html`
3. Enable GitHub Pages in repository settings
4. Access via: `https://yourusername.github.io/repository-name/privacy-policy.html`

#### Option C: Netlify/Vercel (Free)
1. Drag and drop `privacy-policy.html` to Netlify/Vercel
2. Get a free custom URL
3. Access via provided URL

### 3. For Google Play Store

#### Required Steps:
1. **Host the file** on a publicly accessible web server
2. **Get the direct URL** to the privacy policy
3. **Add the URL** to your Google Play Console under:
   - Store Presence → Main Store Listing → Privacy Policy

#### Example URLs for Google Play:
```
https://manschoice.co.ke/privacy-policy.html
https://manschoice.com/privacy
https://your-app-domain.com/privacy-policy
```

### 4. Quick Testing (Current Setup)

Since you're in the development environment, you can:

1. **Open directly in browser:**
   - The file is located at: `/home/smith/Desktop/MAN/manchoice-backend/privacy-policy.html`
   - Right-click → "Open With" → Choose your browser

2. **Start local server:**
   ```bash
   cd /home/smith/Desktop/MAN/manchoice-backend
   python3 -m http.server 8000
   ```
   Then open: `http://localhost:8000/privacy-policy.html`

### 5. File Location
- **HTML File:** `privacy-policy.html` (ready for hosting)
- **Markdown Source:** `PRIVACY_POLICY.md` (source document)

### 6. Next Steps for Production

1. **Choose hosting method** from options above
2. **Update contact information** in the HTML file:
   - Replace `[Your Contact Number]`
   - Replace `[Your Physical Address]`
3. **Test the URL** to ensure it loads correctly
4. **Submit to Google Play** with the live URL

### 7. Troubleshooting

**If the file doesn't open:**
- Ensure you have a web browser installed
- Check file permissions: `chmod 644 privacy-policy.html`
- Try a different browser (Chrome, Firefox, Edge)

**If hosting issues:**
- Verify the file is in the correct directory on your server
- Check that the server can serve HTML files
- Test the URL in an incognito/private window

---

**Need Help?**
- For hosting setup: Contact your web hosting provider
- For Google Play: Refer to Google Play Developer documentation
- For technical issues: Check server logs and file permissions