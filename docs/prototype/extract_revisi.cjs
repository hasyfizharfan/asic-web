const fs = require('fs');
const path = require('path');

const html = fs.readFileSync(path.join(__dirname, 'ASIC-Prototype-Revisi-2027.html'), 'utf8');

// Match all <script type="text/plain" data-role="..." data-page="...">...</script>
const regex = /<script\s+type=["']text\/plain["']\s+data-role=["']([^"']+)["']\s+data-page=["']([^"']+)["']>([\s\S]*?)<\/script>/gi;

let match;
const pages = [];
const pagesDir = path.join(__dirname, 'pages_revisi');
if (!fs.existsSync(pagesDir)) {
    fs.mkdirSync(pagesDir, { recursive: true });
}

while ((match = regex.exec(html)) !== null) {
    const role = match[1];
    const page = match[2];
    const content = match[3];
    pages.push({ role, page });
    const filename = `${role}__${page}.html`;
    fs.writeFileSync(path.join(pagesDir, filename), content.trim());
}

console.log(`Extracted ${pages.length} pages:`);
pages.forEach(p => console.log(`- Role: ${p.role}, Page: ${p.page}`));
