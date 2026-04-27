const fs = require('fs');
const path = require('path');

const articles = [
    'article-bicholim-hub.html',
    'article-institutional-land.html',
    'article-sea-facing-assets.html',
    'insights/mopa-airport-impact.html',
    'insights/nri-investment-guide.html',
    'insights/branded-land-advantage.html',
    'insights/north-vs-south-goa.html'
];

for (const file of articles) {
    const filePath = path.join(__dirname, file);
    if (!fs.existsSync(filePath)) continue;

    let content = fs.readFileSync(filePath, 'utf-8');
    
    const depth = file.includes('insights') ? 1 : 0;
    const root = depth === 0 ? '' : '../';

    // We want to replace the hardcoded display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem; and everything inside it with a JS script.
    
    // Find the start of the <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem;">
    const gridStartStr = `<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem;">`;
    const gridStart = content.lastIndexOf(gridStartStr);
    
    if (gridStart === -1) {
        console.log(`Could not find grid start in ${file}`);
        continue;
    }

    // The grid is basically everything until </div> </div> </article>
    // Let's just find the `</article>` and we know the grid closes right before it.
    const articleEnd = content.indexOf('</article>', gridStart);
    
    if (articleEnd === -1) continue;

    const beforeGrid = content.substring(0, gridStart);
    const afterGrid = content.substring(articleEnd);

    const randomScript = `
      <div id="random-insights-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem;">
        <!-- Filled by JS -->
      </div>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", function() {
        const root = "${root}";
        const currentPath = window.location.pathname;
        
        const allInsights = [
          {
            title: "MOPA Airport Expansion: The Impact on North Goa Property Prices",
            url: root + "insights/mopa-airport-impact.html",
            img: root + "images/ONE GOA/Scene_04_B362_Front_Evening_View_01-scaled.webp",
            id: "mopa-airport-impact.html"
          },
          {
            title: "South Goa vs North Goa: Which is better for Long-term Plot Investment?",
            url: root + "insights/north-vs-south-goa.html",
            img: root + "images/GULF OF GOA/image-4.avif",
            id: "north-vs-south-goa.html"
          },
          {
            title: "Branded Land vs Local Plots: Why HNI Investors are Choosing HOABL",
            url: root + "insights/branded-land-advantage.html",
            img: root + "images/ONE GOA/HoABL-Clubhouse-with-Swimming-Pool-and-Deck-1-quj3wcnzbm0m73dl139y84143rxgeku8axhocs6few.webp",
            id: "branded-land-advantage.html"
          },
          {
            title: "NRI Investment Guide: Why Goa is the Top Choice for the Indian Diaspora",
            url: root + "insights/nri-investment-guide.html",
            img: root + "images/ONE GOA/one-goa-plots-by-hoabl-banner-1-quj0gl8zgwlhjf5bplebxcc8ddxmx9ijad85l7oio8.webp",
            id: "nri-investment-guide.html"
          },
          {
            title: "Why Bicholim is the New North Goa Hub",
            url: root + "article-bicholim-hub.html",
            img: root + "images/ONE GOA/3-Sea-and-Beach-Aerial-scaled.webp",
            id: "article-bicholim-hub.html"
          },
          {
            title: "Sea-Facing Assets: The Ultimate Appreciation Play",
            url: root + "article-sea-facing-assets.html",
            img: root + "images/GULF OF GOA/image-7.avif",
            id: "article-sea-facing-assets.html"
          },
          {
            title: "Institutional vs Unorganized Land: A 10-Year ROI Study",
            url: root + "article-institutional-land.html",
            img: root + "images/ONE GOA/club_jpg_439790de9d.jpg",
            id: "article-institutional-land.html"
          }
        ];

        // Filter out current article
        const availableInsights = allInsights.filter(item => !currentPath.includes(item.id));
        
        // Shuffle and pick 2
        for (let i = availableInsights.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [availableInsights[i], availableInsights[j]] = [availableInsights[j], availableInsights[i]];
        }
        
        const selected = availableInsights.slice(0, 2);
        const grid = document.getElementById('random-insights-grid');
        
        selected.forEach(item => {
          const cardHtml = \`
            <a href="\${item.url}" style="text-decoration: none; display: block; transition: 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
              <div style="height: 200px; border-radius: 16px; overflow: hidden; margin-bottom: 1.5rem;">
                <img src="\${item.img}" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
              <h4 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; color: #fff; margin-bottom: 0.8rem; line-height: 1.4;">\${item.title}</h4>
              <span style="color: #c9a84c; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">Read Article →</span>
            </a>
          \`;
          grid.innerHTML += cardHtml;
        });
      });
    </script>
  `;

    const newContent = beforeGrid + randomScript + afterGrid;
    fs.writeFileSync(filePath, newContent, 'utf-8');
    console.log(`Updated random insights script on ${file}`);
}
