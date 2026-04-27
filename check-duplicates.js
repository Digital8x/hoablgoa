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

    const content = fs.readFileSync(filePath, 'utf-8');
    
    // Count occurrences of Explore Our Collections
    const matchColl = content.match(/Explore Our Collections/g);
    const countColl = matchColl ? matchColl.length : 0;
    
    // Count occurrences of Explore More Insights
    const matchIns = content.match(/Explore More .*Insights/g);
    const countIns = matchIns ? matchIns.length : 0;

    console.log(`${file}: Collections=${countColl}, Insights=${countIns}`);
}
