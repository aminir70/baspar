#!/usr/bin/env python3
"""Count Baspar stylesheets on live pages (no cookies, like a visitor).

Usage: python3 tools/check-css.py [https://basparmarket.com]
Expect exactly 1 Baspar stylesheet per page with Baspar widgets
(uploads/baspar-elements/page-<version>-<hash>.css), or only all.min.css when
BASPAR_ELEMENTS_CSS_ALL is defined, and no fonts.googleapis.com Vazirmatn link.
"""
import re
import sys
import time
import urllib.request

base = (sys.argv[1] if len(sys.argv) > 1 else 'https://basparmarket.com').rstrip('/')
pages = ['/', '/product/dop/', '/epoxy-vs-polyester-resin/', '/shop/', '/product-category/chemical/']
ok = True
for p in pages:
    url = '%s%s?nocache=%d' % (base, p, time.time())
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Linux; Android 11) Mobile'})
    try:
        html = urllib.request.urlopen(req, timeout=60).read().decode('utf-8', 'replace')
    except Exception as e:  # noqa: BLE001
        print('ERR %-30s %s' % (p, e))
        ok = False
        continue
    css = [m.group(1) for m in re.finditer(r"<link[^>]+rel=['\"]stylesheet['\"][^>]+href=['\"]([^'\"]*baspar-elements[^'\"]*)['\"]", html)]
    gf = 'fonts.googleapis.com/css2?family=Vazirmatn' in html
    status = 'OK ' if len(css) <= 1 and not gf else 'BAD'
    ok = ok and status == 'OK '
    print('%s %-30s %d Baspar CSS%s' % (status, p, len(css), ' + Google Vazirmatn CSS' if gf else ''))
    for c in css:
        print('      ', c)
sys.exit(0 if ok else 1)
