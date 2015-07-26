#!/usr/bin/python3
import re
import random
import urllib.parse
import urllib.request
from time import sleep
from sys import exit
from subprocess import Popen, PIPE

# gcat home page and directory for cached pages
baseurl = 'http://binf1.memphis.edu/gcat/'
cachedir = '/var/www/html/gcat/sessions/cache/'
sessiondir = '/var/www/html/gcat/sessions/'

# email content for developers
report = '''subject:GCAT alert

Dear developers of {}:
    Your application is down with the following error message:
    
    {}

    We hope you could look into the problem at your earliest convenience, thank you.

--binfbot
'''

# sys cmd to send email to gcat developers
sendmailcmd = '''sendmail -f no-reply@binf.memphis.edu -t gcat@binf1.memphis.edu '''

# attr=values
attrfind = re.compile(
    r'([a-zA-Z_][-.:a-zA-Z_0-9]*)(\s*=\s*'
    r'(\'[^\']*\'|"[^"]*"|[^\s"\'=<>`]*))')

# prepared query components
genenames = ['a2m', 'atoh1', 'dll1', 'fyn']
organisms = ['mouse', 'human']
years = ['2007', '2010']
subsets = ['all', '430-2']

# get the random generated session id
def grep_attr( htmlfile, attrname):
    sep = re.compile(r'="|=|"')
    htmlstr = htmlfile.read()
    attrs = attrfind.finditer(htmlstr)
    for attr in attrs:
        attrstr = attr.group()
        itemlist = sep.split(attrstr)
        if (itemlist[0]==attrname):
            return itemlist[1]


# form a message and email to app developers
def email_developer(message, appname='GCAT'):
    emailbody = report.format(appname, message)
    sendproc = Popen( sendmailcmd.split(), stdin=PIPE, stdout=PIPE)
    sendproc.communicate(input=emailbody.encode('utf-8'))
    sendproc.stdin.close()


# fetch a local copy of index page for testing comparison
def get_pagecache(pageurl, filename):
    #print ('Fetching page ' + pageurl + ' ...')
    try:
        response = urllib.request.urlopen(pageurl)
    except urllib.error.URLError as urlexception:
        #print ('Page at ' + pageurl + ' is down due to: ' 
        #        + urlexception.reason)
        email_developer(urlexception.reason)

    # catch urlerror here
    # email developer
    with open( cachedir + filename +'.html','w') as idxcache:
        # response is in bytes
        for line in (response.read().decode('utf-8')):
            idxcache.write(line)


# prepare a query string manually for gcat index page
def prepare_indexquery( geneInput='a2m', organism='mouse', subset = 'all', year = '2010', submitButton = 'Submit'):
    dat = {}
    dat['geneInput'] = geneInput
    dat['organism'] = organism
    dat['subset'] = subset
    dat['year'] = year
    dat['submitButton'] = submitButton
    query_str = urllib.parse.urlencode(dat)
    return (baseurl + 'index.py?'+ query_str)

# generate a query string randomly for gcat index page
def random_indexquery():
    dat = {}
    dat['geneInput'] = random.choice(genenames) 
    dat['organism'] = random.choice(organisms)
    dat['subset'] = random.choice(subsets)
    dat['year'] = random.choice(years)
    dat['submitButton'] = 'Submit'
    query_str = urllib.parse.urlencode(dat)
    return (baseurl + 'index.py?'+ query_str)

# return test result
def run_test():
    # set filename
    filename = 'testgcat'
    
    # cache a query result page
    #get_pagecache(prepare_indexquery(), filename)
    get_pagecache(random_indexquery(), filename)
    
    # grep session id from cache
    with open( cachedir + filename + '.html') as cachefile:
        sessionid = grep_attr(cachefile, 'session')
        # check corresponding files in session dir
        try:
            #print (sessiondir + sessionid + '.meta')
            with open( sessiondir + sessionid + '.meta') as metadata:
                #print (metadata.read())
                return True
        except IOError as fileerror:
            #print (fileerror.with_traceback)
            return False


if __name__=='__main__':
    # count failure
    failcount = 0
    while True:
        result = run_test()
        if result==False:
            failcount += 1
        else:
            failcount = 0
            #print('OK')

        # issue alert if 3 concecutive failures
        if (failcount == 3):
            email_developer('Session File Error.')
        sleep(90)
