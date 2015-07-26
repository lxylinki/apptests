#!/usr/bin/python3
import re
from time import sleep
from html.parser import HTMLParser

#tagfindexample = re.compile('[a-zA-Z][-.a-zA-Z0-9:_]*')

#attrfindexample = re.compile(
#    r'\s*([a-zA-Z_][-.:a-zA-Z_0-9]*)(\s*=\s*'
#    r'(\'[^\']*\'|"[^"]*"|[^\s"\'=<>`]*))?')

#tagfind = re.compile(
#    r'\s*([a-zA-Z_][-.:a-zA-Z_0-9]*)(^\s*=\s*'
#    r'(\'[^\']*\'|"[^"]*"|[^\s"\'=<>`]*))?')

attrfind = re.compile(
    r'([a-zA-Z_][-.:a-zA-Z_0-9]*)(\s*=\s*'
    r'(\'[^\']*\'|"[^"]*"|[^\s"\'=<>`]*))')

class MyParser(HTMLParser):
    # extend a default method in HTMLParser
    def handle_starttag(self, tag, attrs):
        if (tag == 'a' or tag == 'td'):
            print ('tag:    ', tag)
            for attr in attrs:
                print ('        ',attr)


sep = re.compile(r'="|=|"|&')

# grep the attribute value
# given tag and attr names
def grepattr( htmlfile, attrname=None):
    htmlstr = htmlfile.read()
    attrs = attrfind.finditer(htmlstr)
    for attr in attrs:
        attrstr = attr.group()
        itemlist = sep.split(attrstr)
        print (itemlist)
        #if (itemlist[0]==attrname):
            #return itemlist[1]


if __name__=='__main__':
    #with open('./index.php') as testfile:
        #grepattr(testfile)
    with open('/tmp/Collaboratum_testquery_result.html') as testfile:
        parser = MyParser()
        parser.feed(testfile.read())
        #grepattr(testfile)
         
