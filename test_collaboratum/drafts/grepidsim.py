#!/usr/bin/python3
import re
import subprocess

# get first match of graph data
getdata_cmd = 'grep -m 1 data: /tmp/Collaboratum_testcache.html'
datastr = subprocess.check_output(getdata_cmd.split(), universal_newlines=True)

#print (datastr)

sep=re.compile(r'{|:|`|"|}|,')
splits = sep.split(datastr)

pairs = []
# filter
for item in splits:
    try:
        item = float(item)
        pairs.append(item)
    # skip strings
    except ValueError:
        continue

print (len(pairs) )
for i in range (1, len(pairs)-1, 2):
    print ((pairs[i+1], pairs[i]))
