import fs from 'fs';
import path from 'path';

const src = fs.readFileSync('docs/prototype/ASIC-Prototype.html', 'utf-8');

// Find all occurrences of tailwind.config
const matches = [...src.matchAll(/tailwind\.config/g)];
console.log('Matches for tailwind.config in ASIC-Prototype.html:', matches.length);
matches.forEach((m, i) => {
  console.log(`Match ${i} at index ${m.index}:`);
  console.log(src.substring(m.index, m.index + 200));
  console.log('-------------------------');
});

// Also check each file in docs/prototype/pages
const pagesDir = 'docs/prototype/pages';
const files = fs.readdirSync(pagesDir);
console.log(`Checking ${files.length} pages in ${pagesDir}...`);
let pageConfigs = 0;
files.forEach(f => {
  const content = fs.readFileSync(path.join(pagesDir, f), 'utf-8');
  if (content.includes('tailwind.config')) {
    pageConfigs++;
    console.log(`Found tailwind.config in ${f}`);
  }
});
console.log(`Total page files with tailwind.config: ${pageConfigs}`);

