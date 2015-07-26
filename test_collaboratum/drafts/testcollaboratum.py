#!/usr/bin/python3
import re
import socket
import random
import urllib.parse
import urllib.request
from time import sleep
from sys import exit
from subprocess import check_call, check_output, Popen, PIPE

# Collaboratum home page and service ports
hostname = 'binf1.memphis.edu'
baseurl = 'http://binf1.memphis.edu/Collaboratum/'
posturl = 'http://binf1.memphis.edu/Collaboratum/views/results.php'
cachedir = '/tmp/'

# 1.get id and similarities from sockets
# 2.match with those in page cache
lsi_port = 50005
keyword_port = 50004

interval = 90

# concecutive failures allowed
maxfails = 3

# email content for developers
report = '''subject:CollaboratUM alert

Dear developers of {}:
    Your application is down with the following error message:
    
    {}

    We hope you could look into the problem at your earliest convenience, thank you.

--binfbot
'''

# sys cmd to send email to gcat developers
sendmailcmd = '''sendmail -f no-reply@binf.memphis.edu -t Collaboratum@binf1.memphis.edu '''


# components for search query
query_terms=['autism', 'breast', 'cancer', 'obesity']
# exact search: true, conceptual: false
is_exact=['true', 'false']
# this is named 'searchType' in query func
filter_types=[0, 1, 2]
#flash_options=['true', 'false']


# form a message and email to app developers
def email_developer(message, appname='CollaboratUM'):
    emailbody = report.format(appname, message)
    sendproc = Popen( sendmailcmd.split(), stdin=PIPE, stdout=PIPE)
    sendproc.communicate(input=emailbody.encode('utf-8'))
    sendproc.stdin.close()


# fetch a local copy of index page for testing comparison
def get_pagecache(pageurl, filename):
    try:
        response = urllib.request.urlopen(pageurl)
    except urllib.error.URLError as urlexception:
        email_developer(urlexception.reason)

    with open( cachedir + filename +'.html','w') as pagecache:
        # response is in bytes
        for line in (response.read().decode('utf-8')):
            pagecache.write(line)

# randomly generate a query data
class raw_query:
    def __init__(self):
        self.term = random.choice(query_terms)
        self.exact_search = random.choice(is_exact)
        self.filter_type = random.choice(filter_types)
        self.is_flash = 'true'


# client testing lsi/kwd services
class test_client:
    MAX_FAILS = 3
    MSGLEN = 2048*4
   
    def __init__(self):
        try:
            sndsock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        except OSError as sock_init_error:
            print (sock_init_error)
            exit(1)
        sndsock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        
        self.sndsock = sndsock
        self.service_fails = 0
    
    # send query, store response in buffer, send ack
    # get the id similarity pairs from socket
    def func_out(self, hostname, port, query_term, query_type):
        # connect to server
        try:
            self.sndsock.connect((hostname, port))
            self.service_fails = 0
        except OSError as sock_conn_error:
            self.service_fails += 1
            if self.service_fails == MAX_FAILS:
                email_developer(sock_conn_error)
            exit(sock_conn_error.errno)
        
        # form query string and send it
        msg_str = "{} | {}".format(query_type, query_term)
        msg_bytes = msg_str.encode('utf-8')
        try:
            self.sndsock.sendall(msg_bytes)
        except OSError as sock_send_error:
            print (sock_send_error)
            exit(sock_send_error.errno)

        # receive ids and similarities from server
        data=''
        try:
            data = self.sndsock.recv(self.MSGLEN)
        except OSError as sock_recv_error:
            print (sock_recv_error)
            exit(sock_recv_error.errno)

        # send ACK and close socket
        ack_str = "ack"
        ack_bytes = ack_str.encode('utf-8')
        try:
            self.sndsock.sendall(ack_bytes)
        except OSError as ack_send_error:
            print (ack_send_error)
            exit(ack_send_error.errno)
        self.sndsock.close()
       
        # return numerical id-similarity pairs
        num_pairs = []
        data_str = data.decode('utf-8')
        newline = re.compile('\n')
        pairs = newline.split(data_str)
        for id_sim in pairs:
            # skip single value at the end
            if (len(id_sim.split()) == 2):
                idval = float(id_sim.split()[0])
                simval = float(id_sim.split()[1])
            
                # result is a list of tuples
                num_pairs.append((idval, simval))
                #print ((idval, simval))
        return num_pairs
    
    # parse the cache for id similarity pairs
    # usually a subset of func_out: not all lines are displayed on page
    def curl_out(self, data2post, cache_filename):
        cache_name = cachedir + cache_filename
        # run curl
        curl_cmd = 'curl --data {} {} -s -o {}'.format(data2post, posturl, cache_filename)
        check_call(curl_cmd.split())

        # get graph data
        getdata_cmd = 'grep -m 1 data: {}'.format(cache_filename)
        datastr = check_output(getdata_cmd.split(), universal_newlines=True)
        sep = re.compile(r'{|:|`|"|}|,')
        splits = sep.split(datastr)
        pairs = []
        num_pairs = []
        # filter out string lines
        for item in splits:
            try:
                item = float(item)
                pairs.append(item)
            except ValueError:
                continue
            
        # skip first value: it is threshold
        for i in range(1, len(pairs)-1, 2):
            num_pairs.append((pairs[i+1], pairs[i]))
        return num_pairs

    # match curl_out with func_out
    def run_test(self, query, cache_name):
        port = ''
        if (query.exact_search == 'true'):
            port = keyword_port
        else:
            port = lsi_port
        
        curl_data = 'searchBox={}&exactSearch={}&searchType={}&isFlashEnabled={}'.format(query.term, query.exact_search, query.filter_type, query.is_flash)

        func_result = self.func_out(hostname, port, query.term, query.filter_type)
        curl_result = self.curl_out(curl_data, cache_name)
        for i in range (0, len(curl_result)):
            if (curl_result[i] != func_result[i]):
                return False
        return True



if __name__=='__main__':
    cache_name = cachedir + 'Collaboratum_testcache.html'
    failcount = 0
    while True:
        servicebot = test_client()
        query = raw_query()
        result = servicebot.run_test(query, cache_name)
        if result == False:
            failcount += 1
        else:
            failcount = 0
        if failcount == maxfails:
            msg = 'Concecutive errors occur in result page rendering.'
            email_developer(msg)
        #print (result, failcount)
        sleep(interval)
    
