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
    
    const ctaIndex = content.indexOf('<div class="cta-box">');
    if (ctaIndex === -1) continue;

    let beforeCta = content.substring(0, ctaIndex);
    let afterCta = content.substring(ctaIndex);

    // If "Explore Our Collections" exists before CTA, it's a duplicate block from the old layout
    // The old block started with <div style="margin-top: 5rem...
    const oldCollIndex = beforeCta.lastIndexOf('<div style="margin-top: 5rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 4rem;">');
    
    if (oldCollIndex !== -1 && beforeCta.substring(oldCollIndex).includes('Explore Our Collections')) {
        // We need to cut out everything from oldCollIndex to the end of beforeCta
        beforeCta = beforeCta.substring(0, oldCollIndex);
        console.log(`Removed duplicate sections before CTA in ${file}`);
    }

    content = beforeCta + afterCta;
    
    // Also let's remove the HTML comments from the appended section so they don't confuse search
    content = content.replace(/<!-- Explore Our Collections -->/g, '');
    content = content.replace(/<!-- Explore More Insights -->/g, '');
    content = content.replace(/<!-- Spacer & Divider -->/g, '');

    fs.writeFileSync(filePath, content, 'utf-8');
}
