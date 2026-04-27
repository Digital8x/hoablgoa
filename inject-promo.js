const fs = require('fs');
const path = require('path');

const files = [
    { file: 'one-goa/index.html', depth: 1 },
    { file: 'gulf-of-goa/index.html', depth: 1 },
];

for (const { file, depth } of files) {
    const filePath = path.join(__dirname, file);
    if (!fs.existsSync(filePath)) {
        console.log(`File not found: ${file}`);
        continue;
    }

    let content = fs.readFileSync(filePath, 'utf8');
    
    if (content.includes('HOABL Nagpur Cross Promotion')) {
        console.log(`Already has promo in ${file}`);
        continue;
    }

    const root = depth === 0 ? '' : '../';

    const promoHtml = `
  <!-- HOABL Nagpur Cross Promotion -->
  <section class="cross-promo-section" style="padding: 4rem 2rem; background: #030812; border-top: 1px solid rgba(255,255,255,0.05);">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
      <h3 style="font-family: 'Playfair Display', serif; font-size: 2.2rem; margin-bottom: 0.5rem; color: #fff;">Explore Our Other Projects</h3>
      <p style="color: #a8b2c1; margin-bottom: 2.5rem;">Discover premium land investments beyond Goa.</p>
      
      <a href="https://hoabl-nagpur.com/" target="_blank" style="text-decoration: none; display: block; max-width: 800px; margin: 0 auto; border-radius: 24px; overflow: hidden; position: relative; border: 1px solid rgba(201,168,76,0.3); transition: 0.4s;" onmouseover="this.style.transform='scale(1.02)'; this.style.borderColor='#c9a84c'" onmouseout="this.style.transform='scale(1)'; this.style.borderColor='rgba(201,168,76,0.3)'">
        <div style="height: 350px; overflow: hidden; position: relative;">
          <img src="${root}images/nagpur-promo.png" style="width: 100%; height: 100%; object-fit: cover; object-position: right center;">
          <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(3,8,18,0.97) 0%, rgba(3,8,18,0.4) 50%, transparent 100%); display: flex; flex-direction: column; justify-content: flex-end; align-items: flex-end; padding: 2.5rem; text-align: right;">
            <span style="background: rgba(201,168,76,0.2); color: #c9a84c; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 1rem; width: fit-content; border: 1px solid rgba(201,168,76,0.5); backdrop-filter: blur(5px);">New Launch</span>
            <h4 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: #fff; margin-bottom: 0.5rem; margin-top: 0;">HOABL Nagpur</h4>
            <p style="color: #e8eaf0; font-size: 1.1rem; margin-bottom: 0;">Premium Branded Land in Maharashtra's Winter Capital</p>
          </div>
        </div>
      </a>
    </div>
  </section>

`;

    let insertIndex = content.lastIndexOf('<footer');
    if (insertIndex === -1) insertIndex = content.lastIndexOf('</body>');
    if (insertIndex === -1) { console.log(`No insertion point in ${file}`); continue; }

    const newContent = content.substring(0, insertIndex) + promoHtml + content.substring(insertIndex);
    fs.writeFileSync(filePath, newContent, 'utf8');
    console.log(`Injected promo in ${file}`);
}

// Also fix the badge position in ALL already-updated files
const allFiles = [
    'index.html',
    'article-bicholim-hub.html',
    'article-institutional-land.html',
    'article-sea-facing-assets.html',
    'insights/mopa-airport-impact.html',
    'insights/nri-investment-guide.html',
    'insights/branded-land-advantage.html',
    'insights/north-vs-south-goa.html',
];

const oldAlign = `justify-content: flex-end; padding: 2.5rem; text-align: left;">
            <span style="background: rgba(201,168,76,0.2); color: #c9a84c; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 1rem; width: fit-content; border: 1px solid rgba(201,168,76,0.5); backdrop-filter: blur(5px);">New Launch</span>
            <h4 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: #fff; margin-bottom: 0.5rem;">HOABL Nagpur</h4>
            <p style="color: #e8eaf0; font-size: 1.1rem; margin-bottom: 0;">Premium Branded Land in Maharashtra's Winter Capital</p>`;

const newAlign = `justify-content: flex-end; align-items: flex-end; padding: 2.5rem; text-align: right;">
            <span style="background: rgba(201,168,76,0.2); color: #c9a84c; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 1rem; width: fit-content; border: 1px solid rgba(201,168,76,0.5); backdrop-filter: blur(5px);">New Launch</span>
            <h4 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: #fff; margin-bottom: 0.5rem; margin-top: 0;">HOABL Nagpur</h4>
            <p style="color: #e8eaf0; font-size: 1.1rem; margin-bottom: 0;">Premium Branded Land in Maharashtra's Winter Capital</p>`;

for (const file of allFiles) {
    const filePath = path.join(__dirname, file);
    if (!fs.existsSync(filePath)) continue;
    let content = fs.readFileSync(filePath, 'utf8');
    if (content.includes(oldAlign)) {
        content = content.replace(oldAlign, newAlign);
        // Also fix object-position so actor stays on left, text on right
        content = content.replace(
            'object-fit: cover; object-position: center;"',
            'object-fit: cover; object-position: right center;"'
        );
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`Fixed badge position in ${file}`);
    } else {
        console.log(`Already fixed or pattern not found in ${file}`);
    }
}
