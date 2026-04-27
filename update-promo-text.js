const fs = require('fs');
const path = require('path');

const files = [
    'index.html',
    'one-goa/index.html',
    'gulf-of-goa/index.html',
    'article-bicholim-hub.html',
    'article-institutional-land.html',
    'article-sea-facing-assets.html',
    'insights/mopa-airport-impact.html',
    'insights/nri-investment-guide.html',
    'insights/branded-land-advantage.html',
    'insights/north-vs-south-goa.html',
];

// Regex to find the badge and the heading
const badgeRegex = /<span style="[^"]*backdrop-filter:\s*blur\(5px\);"[^>]*>New Launch<\/span>/g;
const headingRegex = /<h4 style="[^"]*font-family:\s*'Playfair Display', serif; font-size: 2\.5rem;[^>]*">HOABL Nagpur<\/h4>/g;

for (const file of files) {
    const filePath = path.join(__dirname, file);
    if (!fs.existsSync(filePath)) continue;

    let content = fs.readFileSync(filePath, 'utf8');
    let modified = false;

    if (content.match(badgeRegex)) {
        content = content.replace(badgeRegex, (match) => match.replace('New Launch', '📍 NAGPUR, INDIA'));
        modified = true;
    }

    if (content.match(headingRegex)) {
        content = content.replace(headingRegex, (match) => match.replace('HOABL Nagpur', 'HOABL Nagpur Marina'));
        modified = true;
    }

    if (modified) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`Updated text in ${file}`);
    } else {
        console.log(`Patterns not found in ${file}`);
    }
}
