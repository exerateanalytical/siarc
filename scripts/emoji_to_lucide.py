#!/usr/bin/env python3
# Convert emoji icons in Blade views to Lucide <i data-lucide> icons.
# Safe, idempotent-ish. Leaves navigational arrows, box-drawing and rating stars.
import re, sys, io

# emoji char -> lucide icon name
M = {
 '\U0001F441':'eye','\U0001F4E6':'package','\U0001F4C5':'calendar','\U0001F4AC':'message-circle',
 '\U0001F4C4':'file-text','\U0001F4CA':'bar-chart-3','\U0001F91D':'handshake','\U0001F4B0':'banknote',
 '\U0001F4BB':'laptop','\U0001F3E2':'building-2','\U0001F4CB':'clipboard-list','\U0001F4BC':'briefcase',
 '⭐':'star','\U0001F3D7':'hard-hat','\U0001F3ED':'factory','\U0001F30D':'globe','⚡':'zap',
 '\U0001F4CD':'map-pin','\U0001F33E':'wheat','\U0001F69B':'truck','\U0001F465':'users','\U0001F310':'globe',
 '\U0001F4A1':'lightbulb','⚙':'settings','\U0001F6E0':'wrench','\U0001F3E5':'heart-pulse',
 '\U0001F3DB':'landmark','\U0001F4DE':'phone','\U0001F69A':'truck','✅':'check-circle-2',
 '\U0001F36B':'candy','\U0001F332':'trees','\U0001F3C6':'trophy','\U0001F3E6':'landmark','⬇':'download',
 '\U0001F4C8':'trending-up','\U0001F334':'palmtree','\U0001F680':'rocket','✕':'x','\U0001F514':'bell',
 '\U0001F52C':'microscope','✉':'mail','⛏':'pickaxe','\U0001F50D':'search','\U0001F517':'link',
 '✈':'plane','⚠':'alert-triangle','\U0001F4DA':'book-open','✍':'pen-line','\U0001F9D1':'user',
 '❄':'snowflake','\U0001F3AA':'tent','\U0001F331':'sprout','\U0001F6A2':'ship','\U0001F537':'diamond',
 '\U0001F44D':'thumbs-up','\U0001F4E2':'megaphone','\U0001F4DD':'pen-line','\U0001F3AF':'target',
 '\U0001F4E5':'inbox','\U0001F49A':'heart','\U0001F4E7':'mail','\U0001F4C7':'contact','✗':'x',
 '\U0001F9F5':'spool','\U0001F9FA':'shopping-basket','\U0001F4F1':'smartphone','\U0001F4CC':'pin',
 '\U0001F333':'trees','\U0001F512':'lock','\U0001FAAA':'contact','❌':'x','\U0001F4D1':'files',
 '\U0001F4B5':'banknote','\U0001F528':'hammer','✦':'sparkles','\U0001F457':'shirt','\U0001F469':'user',
 '\U0001F393':'graduation-cap','\U0001F957':'salad','➕':'plus','\U0001F522':'hash','\U0001F477':'hard-hat',
 '\U0001F4BD':'save','\U0001F5C2':'folder','\U0001F4C1':'folder','\U0001F4F7':'camera','\U0001F4E9':'mail-open',
 '\U0001F3A4':'mic','\U0001F4F9':'video','\U0001F6AA':'door-open','\U0001F9FE':'receipt','\U0001F4F0':'newspaper',
 '\U0001F6CD':'shopping-bag','\U0001F3EA':'store','\U0001F6B0':'glass-water','⚓':'anchor',
 '\U0001F4B3':'credit-card','\U0001F517':'link','\U0001F4B8':'banknote','\U0001F9EA':'flask-conical',
 '\U0001F4D6':'book-open','\U0001F4C9':'trending-down','\U0001F9F0':'wrench','\U0001F6E1':'shield',
 '\U0001F3DF':'building','\U0001F30B':'mountain','⬆':'arrow-up','\U0001F50B':'battery-charging',
 '\U0001F4A7':'droplet','\U0001F525':'flame','\U0001F4C6':'calendar','✏':'pencil','\U0001F4CE':'paperclip',
 '\U0001F511':'key','\U0001F464':'user','\U0001F4F2':'smartphone','\U0001F310':'globe','\U0001F9ED':'compass',
}
# things to leave as-is: arrows, box-drawing, rating stars
SKIP = set('←→↗↖↘↙─★☆½↑↓●○▪')

LIC = ' class="lic"'

def lucide(name): return '<i data-lucide="%s"%s></i>' % (name, LIC)

def convert(text):
    text = text.replace('️','')  # strip variation selectors first
    # 1) wrap icon-map render sites:  {{ $fooIcons[expr]??'x' }}  -> <i data-lucide="{{ ... }}" class="lic"></i>
    def wrap(m):
        return '<i data-lucide="{{ %s }}"%s></i>' % (m.group(1).strip(), LIC)
    text = re.sub(r"\{\{\s*(\$\w*[Ii]cons?\[[^\}]*?)\s*\}\}", wrap, text)
    # 2) emoji inside single quotes (array values + fallbacks) -> 'lucidename'
    def qrepl(m):
        ch = m.group(1)
        return "'%s'" % M.get(ch, '') if ch in M else m.group(0)
    for ch, name in M.items():
        text = text.replace("'%s'" % ch, "'%s'" % name)
    # 3) bare emoji in HTML text -> lucide icon
    for ch, name in M.items():
        text = text.replace(ch, lucide(name))
    # cleanup stray variation selectors left dangling before our <i>
    text = text.replace('️', '')
    return text

if __name__ == '__main__':
    changed = 0
    for path in sys.argv[1:]:
        with io.open(path, encoding='utf-8') as f: src = f.read()
        out = convert(src)
        if out != src:
            with io.open(path, 'w', encoding='utf-8') as f: f.write(out)
            changed += 1
            print('converted', path)
        else:
            print('no-change', path)
    print('files changed:', changed)
