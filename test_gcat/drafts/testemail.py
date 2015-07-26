#!/usr/bin/python3
import re
import random
from copy import deepcopy
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
report = '''subject:GCAT alert:

Dear developers of {}:
    Your application is down with the following error message:
    
    {}.

    We hope you could look into the problem at your earliest convenience, thank you.

--binfbot
'''

# sys cmd to send email to gcat developers
sendmailcmd = '''sendmail -f no-reply@binf.memphis.edu -t xlin@memphis.edu '''


# form a message and email to app developers
def email_developer(message, appname='GCAT'):
    emailbody = report.format(appname, message)
    sendproc = Popen( sendmailcmd.split(), stdin=PIPE, stdout=PIPE)
    sendproc.communicate(input=emailbody.encode('utf-8'))
    sendproc.stdin.close()


if __name__=='__main__':
    email_developer('Session File Error.')
