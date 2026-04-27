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

    let content = fs.readFileSync(filePath, 'utf8');
    
    // Change max-width of content container
    content = content.replace('.content-container { max-width: 800px;', '.content-container { max-width: 1200px;');

    // Add new grid CSS
    const newCss = `
    /* New Layout CSS */
    .layout-grid { display: grid; grid-template-columns: 1fr 380px; gap: 5rem; align-items: start; margin-top: 3rem; }
    .sidebar { position: sticky; top: 120px; }
    .sticky-cta { background: rgba(3,8,18,0.95); border: 1px solid rgba(201,168,76,0.4); padding: 3rem 2.5rem; border-radius: 24px; box-shadow: 0 25px 50px rgba(0,0,0,0.6); text-align: left; position: relative; overflow: hidden; }
    .sticky-cta::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--primary); }
    .sticky-cta h3 { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 0.8rem; line-height: 1.2; }
    .sticky-cta p { font-size: 1rem; margin-bottom: 2rem; color: #a8b2c1; line-height: 1.5; }
    .sticky-cta input { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.15); width: 100%; box-sizing: border-box; transition: 0.3s; }
    .sticky-cta input:focus { border-color: var(--primary); background: rgba(255,255,255,0.08); }
    .sticky-cta .btn-cta { width: 100%; margin-top: 0.5rem; font-size: 1.1rem; padding: 1.2rem; }
    
    .article-header { border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 2rem; margin-bottom: 2rem; text-align: left; }
    .article-header h1 { margin-bottom: 1rem; }
    .article-header .meta-info { margin-bottom: 0; border: none; padding: 0; }
    
    @media (max-width: 992px) {
      .layout-grid { grid-template-columns: 1fr; gap: 3rem; }
      .sidebar { position: static; }
    }
`;
    // Prevent adding css twice if script is run multiple times
    if (!content.includes('.layout-grid { display: grid;')) {
        content = content.replace('</style>', newCss + '\n  </style>');
    }

    // Find HTML boundaries
    const tagStart = content.indexOf('<span class="article-tag">');
    const bodyStart = content.indexOf('<div class="article-body">');
    const ctaStart = content.indexOf('<div class="cta-box">');
    const formEnd = content.indexOf('</form>', ctaStart);
    const ctaEnd = content.indexOf('</div>', formEnd) + 6;
    const authorStart = content.indexOf('<div class="author-box"');
    const authorEnd = content.indexOf('<!-- Explore Our Collections -->');

    if (tagStart === -1 || bodyStart === -1 || ctaStart === -1 || authorStart === -1 || authorEnd === -1) {
        console.log(`Missing boundaries in ${file}`);
        continue;
    }

    // Extract chunks
    const headerHtml = content.substring(tagStart, bodyStart);
    const bodyHtml = content.substring(bodyStart, ctaStart);
    let ctaHtml = content.substring(ctaStart, ctaEnd);
    const authorHtml = content.substring(authorStart, authorEnd);

    // If it's already updated, skip
    if (content.includes('<div class="layout-grid">')) {
        console.log(`Already updated ${file}`);
        continue;
    }

    // Modify CTA box
    ctaHtml = ctaHtml.replace('class="cta-box"', 'class="cta-box sticky-cta"');
    ctaHtml = ctaHtml.replace(/style="[^"]*max-width:\s*400px;[^"]*"/g, 'style="display: flex; flex-direction: column; gap: 1.2rem;"');

    const layoutHtml = `
    <div class="article-header">
      ${headerHtml.trim()}
    </div>
    
    <div class="layout-grid">
      <div class="main-content">
        ${bodyHtml.trim()}
        ${authorHtml.trim()}
      </div>
      
      <aside class="sidebar">
        ${ctaHtml.trim()}
      </aside>
    </div>

    `;

    const newContent = content.substring(0, tagStart) + layoutHtml + content.substring(authorEnd);
    fs.writeFileSync(filePath, newContent, 'utf8');
    console.log(`Updated layout in ${file}`);
}
