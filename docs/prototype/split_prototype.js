import fs from 'fs';
import path from 'path';

const srcPath = process.argv[2] || 'docs/prototype/ASIC-Prototype.html';
const outDir = process.argv[3] || 'docs/prototype/pages';

const src = fs.readFileSync(srcPath, 'utf-8');
const yearMatch = src.match(/CONF_YEAR\s*=\s*'(\d+)'/);
const year = yearMatch ? yearMatch[1] : '2027';

if (!fs.existsSync(outDir)) {
    fs.mkdirSync(outDir, { recursive: true });
}

const pattern = /<script type="text\/plain" data-role="(\w+)" data-page="(\w+)">([\s\S]*?)<\/script>/g;
let count = 0;
let match;
while ((match = pattern.exec(src)) !== null) {
    const role = match[1];
    const page = match[2];
    const body = match[3];
    const html = body.replace(/^\n+|\n+$/g, '')
        .replaceAll('<§/', '</')
        .replaceAll('<§!--', '<!--')
        .replaceAll('{{YEAR}}', year);
    fs.writeFileSync(path.join(outDir, `${role}__${page}.html`), html, 'utf-8');
    count++;
}
console.log(`${count} halaman ditulis ke ${outDir}`);

