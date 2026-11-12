import fs from 'fs';
import path from 'path';

const pagesDir = 'docs/prototype/pages';
const files = fs.readdirSync(pagesDir).filter(f => f.endsWith('.html'));

const allConfigs = {};
const allColors = new Map();
const allFontSizes = new Map();
const allFontFamilies = new Map();
const allSpacings = new Map();
const allBorderRadius = new Map();

for (const file of files) {
  const content = fs.readFileSync(path.join(pagesDir, file), 'utf-8');
  const idx = content.indexOf('tailwind.config');
  if (idx === -1) continue;

  const scriptEnd = content.indexOf('</script>', idx);
  let snippet = content.substring(idx, scriptEnd).trim();
  // Strip tailwind.config =
  snippet = snippet.replace(/^tailwind\.config\s*=\s*/, '');
  if (snippet.endsWith(';')) snippet = snippet.slice(0, -1);

  try {
    const cfg = new Function(`return (${snippet});`)();
    allConfigs[file] = cfg;

    const extend = cfg.theme?.extend || {};
    if (extend.colors) {
      for (const [k, v] of Object.entries(extend.colors)) {
        if (!allColors.has(k)) allColors.set(k, new Set());
        allColors.get(k).add(typeof v === 'string' ? v.toLowerCase() : JSON.stringify(v));
      }
    }
    if (extend.fontSize) {
      for (const [k, v] of Object.entries(extend.fontSize)) {
        if (!allFontSizes.has(k)) allFontSizes.set(k, new Set());
        allFontSizes.get(k).add(JSON.stringify(v));
      }
    }
    if (extend.fontFamily) {
      for (const [k, v] of Object.entries(extend.fontFamily)) {
        if (!allFontFamilies.has(k)) allFontFamilies.set(k, new Set());
        allFontFamilies.get(k).add(JSON.stringify(v));
      }
    }
    if (extend.spacing) {
      for (const [k, v] of Object.entries(extend.spacing)) {
        if (!allSpacings.has(k)) allSpacings.set(k, new Set());
        allSpacings.get(k).add(JSON.stringify(v));
      }
    }
    if (extend.borderRadius) {
      for (const [k, v] of Object.entries(extend.borderRadius)) {
        if (!allBorderRadius.has(k)) allBorderRadius.set(k, new Set());
        allBorderRadius.get(k).add(JSON.stringify(v));
      }
    }
  } catch (err) {
    console.error(`Error parsing config in ${file}:`, err.message);
  }
}

console.log(`Successfully parsed ${Object.keys(allConfigs).length} page configs!`);

console.log('\n--- COLOR DIFFERENCES (keys with multiple values across pages) ---');
for (const [k, vals] of allColors.entries()) {
  if (vals.size > 1) {
    console.log(`Color [${k}]: ${Array.from(vals).join(' vs ')}`);
  }
}

console.log('\n--- ALL UNIQUE COLORS DEFINED ---');
for (const [k, vals] of allColors.entries()) {
  console.log(`  "${k}": "${Array.from(vals)[0]}",`);
}

console.log('\n--- FONT SIZES DEFINED ACROSS CONFIGS ---');
for (const [k, vals] of allFontSizes.entries()) {
  console.log(`fontSize [${k}]: ${Array.from(vals).join(' | ')}`);
}

console.log('\n--- FONT FAMILIES DEFINED ACROSS CONFIGS ---');
for (const [k, vals] of allFontFamilies.entries()) {
  console.log(`fontFamily [${k}]: ${Array.from(vals).join(' | ')}`);
}

console.log('\n--- SPACING DEFINED ---');
for (const [k, vals] of allSpacings.entries()) {
  console.log(`spacing [${k}]: ${Array.from(vals).join(' | ')}`);
}

console.log('\n--- BORDER RADIUS DEFINED ---');
for (const [k, vals] of allBorderRadius.entries()) {
  console.log(`borderRadius [${k}]: ${Array.from(vals).join(' | ')}`);
}

// Find classes used in markup that are custom and NOT in configs or standard Tailwind
console.log('\n--- CHECKING CLASSES USED IN ALL HTML MARKUP ---');
const usedClasses = new Set();
for (const file of files) {
  const content = fs.readFileSync(path.join(pagesDir, file), 'utf-8');
  const classMatches = content.matchAll(/class=["']([^"']+)["']/g);
  for (const m of classMatches) {
    m[1].split(/\s+/).forEach(c => {
      if (c) usedClasses.add(c);
    });
  }
}

// Check which files use font-code-sm, text-code-sm, code-md etc.
const codeSmUsers = [];
const codeMdUsers = [];
for (const file of files) {
  const content = fs.readFileSync(path.join(pagesDir, file), 'utf-8');
  if (content.includes('code-sm')) codeSmUsers.push(file);
  if (content.includes('code-md')) codeMdUsers.push(file);
}
console.log(`\nPages using code-sm (${codeSmUsers.length}):`, codeSmUsers);
console.log(`Pages using code-md (${codeMdUsers.length}):`, codeMdUsers);

// Check color diff by page group (portal roles: p, s, t, a)
const roleColors = { p: new Set(), s: new Set(), t: new Set(), a: new Set() };
for (const [file, cfg] of Object.entries(allConfigs)) {
  const role = file.split('__')[0];
  const bg = cfg.theme?.extend?.colors?.['background'] || cfg.theme?.extend?.colors?.['surface'];
  const primary = cfg.theme?.extend?.colors?.['primary'];
  const secondary = cfg.theme?.extend?.colors?.['secondary'];
  if (roleColors[role]) {
    roleColors[role].add(`primary:${primary}, secondary:${secondary}, bg:${bg}`);
  }
}
console.log('\n--- ROLE PALETTES ---');
for (const [r, set] of Object.entries(roleColors)) {
  console.log(`Role [${r}]:`, Array.from(set));
}
