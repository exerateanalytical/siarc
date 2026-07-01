#!/usr/bin/env python3
# Second pass: remaining emoji icons not covered by the first map.
import re, sys, io

M = {
 '✓':'check','⛑':'hard-hat','\U0001F6C3':'stamp','\U0001F501':'repeat','\U0001F44B':'hand',
 '⛰':'mountain','\U0001F4FA':'tv','\U0001F44E':'thumbs-down','\U0001F4DC':'scroll','\U0001F50E':'search',
 '\U0001F7E0':'circle','\U0001F4D5':'book','\U0001F3AC':'clapperboard','\U0001F96C':'leaf','☢':'radiation',
 '\U0001F6E2':'fuel','\U0001F404':'beef','\U0001F3AB':'ticket','\U0001F51D':'arrow-up','\U0001F4D0':'ruler',
 '\U0001F69C':'tractor','\U0001FA9A':'axe','\U0001FA91':'armchair','\U0001F9F4':'flask-conical','\U0001F6D2':'shopping-cart',
 '\U0001F510':'lock','\U0001F33F':'leaf','\U0001F3A8':'palette','✂':'scissors','\U0001F916':'bot',
 '\U0001F5FA':'map','\U0001F3A9':'briefcase','\U0001F504':'refresh-cw','♻':'recycle','\U0001F9BA':'shield',
 '\U0001F468':'user','\U0001F6AB':'ban','\U0001F947':'medal','\U0001F948':'medal','\U0001F949':'medal',
 '\U0001F6E1':'shield','\U0001F4C4':'file-text','\U0001F9F1':'blocks','\U0001F3C5':'medal','\U0001F94A':'shield',
 '\U0001F4BE':'save','\U0001F6A7':'construction','\U0001F389':'party-popper','\U0001F550':'clock','\U0001F4ED':'inbox',
 '⚖':'scale','\U0001F31F':'sparkles','\U0001F516':'bookmark','\U0001F937':'help-circle',
}
LIC = ' class="lic"'

def convert(text):
    text = text.replace('️', '')
    def wrap(m): return '<i data-lucide="{{ %s }}"%s></i>' % (m.group(1).strip(), LIC)
    text = re.sub(r"\{\{\s*(\$\w*[Ii]cons?\[[^\}]*?)\s*\}\}", wrap, text)
    for ch, name in M.items():
        text = text.replace("'%s'" % ch, "'%s'" % name)
    for ch, name in M.items():
        text = text.replace(ch, '<i data-lucide="%s"%s></i>' % (name, LIC))
    return text

if __name__ == '__main__':
    changed = 0
    for path in sys.argv[1:]:
        with io.open(path, encoding='utf-8') as f: src = f.read()
        out = convert(src)
        if out != src:
            with io.open(path, 'w', encoding='utf-8') as f: f.write(out)
            changed += 1
    print('files changed:', changed)
