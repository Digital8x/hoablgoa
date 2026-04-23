const pdf = require('pdf-parse');
const fs = require('fs');

const dataBuffer = fs.readFileSync('HOABL-Goa-SEO-Audit-Report (1).pdf');
pdf(dataBuffer).then(function(data) {
    fs.writeFileSync('seo_report_text.txt', data.text);
    console.log('Done! Pages:', data.numpages);
    console.log(data.text);
}).catch(err => console.error('Error:', err.message));
