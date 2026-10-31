#!/bin/sh
# Rebuild the generated assets after editing
#   assets/css/baspar-elements.css  or  assets/js/baspar-elements.js
# CSS: python3 tools/build-css.py   JS: terser (npx terser, or set TERSER=path)
set -e
cd "$(dirname "$0")/.."
python3 tools/build-css.py
mkdir -p assets/js/dist
${TERSER:-npx --yes terser@5.31.0} assets/js/baspar-elements.js --compress --mangle --comments false \
	-o assets/js/dist/baspar-elements.min.js
echo "built: assets/css/dist/*.min.css, assets/js/dist/baspar-elements.min.js"
