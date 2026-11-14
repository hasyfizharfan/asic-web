#!/usr/bin/env bash
# Memeriksa bahwa pekerjaan frontend tidak menyentuh wilayah backend.
# Pemakaian: bash docs/frontend-handoff/check-fe-scope.sh [base-ref]   (default: origin/main, lalu main)
set -uo pipefail

BASE="${1:-origin/main}"
git rev-parse --verify -q "$BASE" >/dev/null || BASE="main"
MERGE_BASE="$(git merge-base "$BASE" HEAD)" || { echo "Tidak bisa menentukan merge-base dengan $BASE"; exit 2; }

# File BARU yang boleh dibuat frontend
ALLOWED_NEW='^(resources/(views|css|js|private)/|public/(images|downloads|fonts)/asic/|lang/[^/]+/asic-[^/]+\.php$|docs/|routes/fe-preview\.php$)'
# File bersama: boleh diubah HANYA dengan penambahan (diperiksa manual)
SHARED='^(package\.json|package-lock\.json|vite\.config\.(js|ts|mjs)|tailwind\.config\.(js|cjs|mjs|ts)|postcss\.config\.(js|cjs|mjs))$'

fail=0; warn=0
check() {
  local status="$1" path="$2"
  case "$status" in
    A*)
      if [[ "$path" =~ $ALLOWED_NEW || "$path" =~ $SHARED ]]; then :; else
        echo "DILARANG   file baru di luar wilayah FE : $path"; fail=1; fi ;;
    M*)
      if [[ "$path" =~ ^docs/ ]]; then :;
      elif [[ "$path" =~ $SHARED ]]; then echo "PERIKSA    file bersama diubah (harus hanya penambahan): $path"; warn=1;
      else echo "DILARANG   mengubah file yang sudah ada : $path"; fail=1; fi ;;
    D*|R*|C*|T*)
      echo "DILARANG   hapus/pindah/ubah-tipe file yang sudah ada : $path"; fail=1 ;;
  esac
}

# 1) tracked: perbandingan merge-base -> working tree (mencakup commit, staged, dan unstaged)
while IFS=$'\t' read -r status path rest; do
  [[ -z "${status:-}" ]] && continue
  check "$status" "$path"
done < <(git diff --name-status "$MERGE_BASE")

# 2) untracked (belum di-git add)
while IFS= read -r path; do
  [[ -z "$path" ]] && continue
  check "A" "$path"
done < <(git ls-files --others --exclude-standard)

# 3) bukan file tergenerate
if git ls-files --others --exclude-standard | grep -qE '^(public/build/|node_modules/|vendor/)'; then
  echo "DILARANG   hasil build/dependensi masuk ke git (public/build, node_modules, vendor)"; fail=1
fi

echo "-----"
if [[ $fail -ne 0 ]]; then echo "GAGAL: ada perubahan di luar wilayah frontend."; exit 1; fi
[[ $warn -ne 0 ]] && echo "LULUS dengan catatan: periksa file bersama di atas secara manual (git diff -- <file>)."
[[ $warn -eq 0 ]] && echo "LULUS: semua perubahan berada di wilayah frontend."
exit 0

