"""Finds colour utilities in a Blade tree that have no dark counterpart.

A `bg-[#FFF]` with no `dark:bg-…` beside it is a box that stays light when the
page goes dark. This walks every class list, pairs each light colour utility
with the dark variant of the same property+state, and reports the orphans.

Not a linter for humans to satisfy blindly: plenty of orphans are correct
(`text-white` on a green button, a token class the ui-kit already re-points).
It is a worklist, not a verdict.

    python scripts/dark-audit.py resources/views/pages/dashboard [--per-file]
"""
import re
import sys
import pathlib
from collections import Counter, defaultdict

PROPS = r'(?:bg|text|border|from|via|to|ring|divide|placeholder|decoration|outline|shadow|fill|stroke|caret|accent)'
# A colour-bearing utility: property, optional -side, then an arbitrary value or
# a palette step. Bare `border` / `text-sm` etc. do not match.
UTIL = re.compile(
    r'(?P<variants>(?:[a-z0-9.:\[\]/%-]+:)*)'
    r'(?P<prop>' + PROPS + r')'
    r'(?P<side>-[xytrbles]{1,2})?'
    r'-(?P<value>\[#[0-9A-Fa-f]{3,8}\]|white|black|(?:slate|gray|grey|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|brand|forest|leaf)(?:-\d{2,3})?)'
    r'(?P<alpha>/\d+)?$'
)
# These re-point themselves through CSS variables, so a dark sibling would be drift.
TOKEN_VALUES = {'brand', 'forest', 'leaf'}


def audit(paths):
    orphans = defaultdict(list)
    for path in paths:
        text = path.read_text(encoding='utf-8', errors='replace')
        for m in re.finditer(r'class="([^"]*)"', text):
            classes = m.group(1)
            line = text.count('\n', 0, m.start()) + 1
            have = set()
            light = []
            for token in re.split(r'\s+', classes):
                token = token.strip()
                hit = UTIL.match(token)
                if not hit:
                    continue
                variants = hit.group('variants')
                key = (
                    variants.replace('dark:', ''),
                    hit.group('prop'),
                    hit.group('side') or '',
                )
                if 'dark:' in variants:
                    have.add(key)
                else:
                    light.append((key, token, hit.group('value')))
            for key, token, value in light:
                if value in TOKEN_VALUES or key in have:
                    continue
                orphans[str(path)].append((line, token))
    return orphans


def main():
    args = [a for a in sys.argv[1:] if not a.startswith('--')]
    per_file = '--per-file' in sys.argv
    roots = [pathlib.Path(a) for a in args] or [pathlib.Path('resources/views')]
    files = []
    for root in roots:
        files.extend(sorted(root.rglob('*.blade.php')) if root.is_dir() else [root])

    orphans = audit(files)
    total = sum(len(v) for v in orphans.values())
    if per_file:
        for path, hits in sorted(orphans.items(), key=lambda kv: -len(kv[1])):
            print(f'{len(hits):4d}  {path}')
            for line, token in hits:
                print(f'        {line}: {token}')
    else:
        for path, hits in sorted(orphans.items(), key=lambda kv: -len(kv[1])):
            print(f'{len(hits):4d}  {path}')
        counts = Counter(t for hits in orphans.values() for _, t in hits)
        print('\nmost common orphan utilities:')
        for token, n in counts.most_common(25):
            print(f'  {n:4d}  {token}')
    print(f'\n{total} orphan colour utilities in {len(orphans)} files '
          f'(of {len(files)} scanned)')


if __name__ == '__main__':
    main()
