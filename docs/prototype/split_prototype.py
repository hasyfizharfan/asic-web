"""python split_prototype.py docs/prototype/ASIC-Prototype.html docs/prototype/pages"""
import re, sys, pathlib

src_path, out_dir = pathlib.Path(sys.argv[1]), pathlib.Path(sys.argv[2])
src = src_path.read_text(encoding="utf-8")
year = (re.search(r"CONF_YEAR\s*=\s*'(\d+)'", src) or [None, "2027"])[1]
out_dir.mkdir(parents=True, exist_ok=True)

pattern = r'<script type="text/plain" data-role="(\w+)" data-page="(\w+)">(.*?)</script>'
count = 0
for role, page, body in re.findall(pattern, src, re.S):
    html = body.strip("\n").replace("<§/", "</").replace("<§!--", "<!--").replace("{{YEAR}}", year)
    (out_dir / f"{role}__{page}.html").write_text(html, encoding="utf-8")
    count += 1
print(f"{count} halaman ditulis ke {out_dir}")

