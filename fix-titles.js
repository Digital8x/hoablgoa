const fs = require('fs');
const path = require('path');

const articles = [
    'article-bicholim-hub.html',
    'article-institutional-land.html',
    'article-sea-facing-assets.html',
    'insights/mopa-airport-impact.html',
    'insights/nri-investment-guide.html',
    'insights/premium-land-vs-apartments.html', // Note: I'll handle the missing ones gracefully
    'insights/rental-yields-in-goa.html',
    'insights/branded-land-advantage.html',
    'insights/north-vs-south-goa.html'
];

for (const file of articles) {
    const filePath = path.join(__dirname, file);
    if (!fs.existsSync(filePath)) {
        continue;
    }

    let content = fs.readFileSync(filePath, 'utf-8');
    
    // Fix the first article title
    content = content.replace(
        /<h4[^>]*>South Goa vs North Goa: Which is better\?<\/h4>/g,
        `<h4 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; color: #fff; margin-bottom: 0.8rem; line-height: 1.4;">Sea-Facing Assets: The Ultimate Appreciation Play</h4>`
    );

    // Fix the second article title
    content = content.replace(
        /<h4[^>]*>Branded Land vs Local Plots<\/h4>/g,
        `<h4 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; color: #fff; margin-bottom: 0.8rem; line-height: 1.4;">Institutional vs Unorganized Land: A 10-Year ROI</h4>`
    );

    fs.writeFileSync(filePath, content, 'utf-8');
    console.log(`Fixed titles in ${file}`);
}
