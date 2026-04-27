const fs = require('fs');
const path = require('path');

const articles = [
    { file: 'insights/branded-land-advantage.html', depth: 1 },
    { file: 'insights/north-vs-south-goa.html', depth: 1 }
];

for (const { file, depth } of articles) {
    const filePath = path.join(__dirname, file);
    if (!fs.existsSync(filePath)) {
        console.log(`Skipping ${file} - not found`);
        continue;
    }

    let content = fs.readFileSync(filePath, 'utf-8');
    
    // Find the end of cta-box
    const ctaStart = content.indexOf('<div class="cta-box">');
    if (ctaStart === -1) {
        console.log(`Could not find cta-box in ${file}`);
        continue;
    }

    // Find the matching closing div for cta-box
    let depthCount = 1;
    let curr = ctaStart + '<div class="cta-box">'.length;
    while (depthCount > 0 && curr < content.length) {
        const nextDiv = content.indexOf('<div', curr);
        const nextClose = content.indexOf('</div>', curr);
        
        if (nextClose === -1) break;
        
        if (nextDiv !== -1 && nextDiv < nextClose) {
            depthCount++;
            curr = nextDiv + 4;
        } else {
            depthCount--;
            curr = nextClose + 6;
        }
    }
    
    const ctaEnd = curr;
    const articleEnd = content.indexOf('</article>', ctaEnd);
    
    if (articleEnd === -1) {
        console.log(`Could not find </article> in ${file}`);
        continue;
    }

    const root = depth === 0 ? '' : '../';

    const appendHtml = `

    <div class="author-box" style="display: flex; align-items: center; gap: 1.5rem; margin-top: 5rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 3rem; margin-bottom: 3rem;">
      <div class="author-img" style="width: 70px; height: 70px; border-radius: 50%; background: #232232; display: flex; align-items: center; justify-content: center; font-size: 2rem; border: 1px solid #c9a84c; color: #8a7ab0; box-shadow: inset 0 0 10px rgba(0,0,0,0.5);">👤</div>
      <div>
        <p style="margin: 0; color: #fff; font-weight: 700; font-size: 1.1rem;">HOABL Project Expert</p>
        <p style="margin: 0; font-size: 0.9rem; color: #a8b2c1;">Investment Analyst & Goa Real Estate Specialist</p>
      </div>
    </div>

    <!-- Explore Our Collections -->
    <div style="margin-top: 5rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 4rem;">
      <h3 style="font-family: 'Playfair Display', serif; font-size: 2.2rem; margin-bottom: 2.5rem; text-align: center;">Explore Our Collections</h3>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        
        <a href="${root}one-goa/" style="text-decoration: none; border-radius: 24px; overflow: hidden; background: rgba(3,8,18,0.6); border: 1px solid rgba(255,255,255,0.1); transition: 0.4s; display: block; position: relative;" onmouseover="this.style.borderColor='#c9a84c'; this.style.transform='scale(1.02)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.transform='scale(1)'">
          <div style="height: 200px; overflow: hidden;">
            <img src="${root}images/ONE GOA/Scene_02_B362_Evening_View03-scaled.webp" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <div style="padding: 1.5rem; background: linear-gradient(to top, rgba(3,8,18,0.9), transparent); position: absolute; bottom: 0; left: 0; right: 0;">
            <h4 style="color: #c9a84c; margin-bottom: 0.3rem; font-family: 'Playfair Display', serif; font-size: 1.3rem;">HOABL One Goa</h4>
            <p style="font-size: 0.8rem; color: #fff; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.9;">Island Township · Bicholim</p>
          </div>
        </a>

        <a href="${root}gulf-of-goa/" style="text-decoration: none; border-radius: 24px; overflow: hidden; background: rgba(3,8,18,0.6); border: 1px solid rgba(255,255,255,0.1); transition: 0.4s; display: block; position: relative;" onmouseover="this.style.borderColor='#c9a84c'; this.style.transform='scale(1.02)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.transform='scale(1)'">
          <div style="height: 200px; overflow: hidden;">
            <img src="${root}images/GULF OF GOA/image-4.avif" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <div style="padding: 1.5rem; background: linear-gradient(to top, rgba(3,8,18,0.9), transparent); position: absolute; bottom: 0; left: 0; right: 0;">
            <h4 style="color: #c9a84c; margin-bottom: 0.3rem; font-family: 'Playfair Display', serif; font-size: 1.3rem;">HOABL Gulf of Goa</h4>
            <p style="font-size: 0.8rem; color: #fff; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.9;">Sea-Facing Plots · Vasco</p>
          </div>
        </a>

      </div>
    </div>

    <!-- Spacer & Divider -->
    <div style="margin: 6rem 0; height: 1px; background: linear-gradient(to right, transparent, rgba(255,255,255,0.1), transparent);"></div>

    <!-- Explore More Insights -->
    <div class="more-insights-section" style="padding-bottom: 4rem;">
      <div style="text-align: center; margin-bottom: 3rem;">
        <span style="color: #c9a84c; text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.75rem; font-weight: 700;">Continue Reading</span>
        <h3 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-top: 0.5rem;">Explore More <span style="color: #c9a84c;">Insights</span></h3>
      </div>
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem;">
        
        <a href="${root}article-sea-facing-assets.html" style="text-decoration: none; display: block; transition: 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
          <div style="height: 200px; border-radius: 16px; overflow: hidden; margin-bottom: 1.5rem;">
            <img src="${root}images/GULF OF GOA/image-4.avif" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <h4 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; color: #fff; margin-bottom: 0.8rem; line-height: 1.4;">South Goa vs North Goa: Which is better?</h4>
          <span style="color: #c9a84c; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">Read Article →</span>
        </a>

        <a href="${root}article-institutional-land.html" style="text-decoration: none; display: block; transition: 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
          <div style="height: 200px; border-radius: 16px; overflow: hidden; margin-bottom: 1.5rem;">
            <img src="${root}images/ONE GOA/club_jpg_439790de9d.jpg" style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <h4 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; color: #fff; margin-bottom: 0.8rem; line-height: 1.4;">Branded Land vs Local Plots</h4>
          <span style="color: #c9a84c; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">Read Article →</span>
        </a>

      </div>
    </div>
  `;

    const newContent = content.slice(0, ctaEnd) + appendHtml + content.slice(articleEnd);
    fs.writeFileSync(filePath, newContent, 'utf-8');
    console.log(`Updated ${file}`);
}
