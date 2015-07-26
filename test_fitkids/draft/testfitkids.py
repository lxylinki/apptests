#!/usr/bin/python3
import random
import urllib.request
import urllib.parse
import concurrent.futures
from sys import exit
from subprocess import check_call, CalledProcessError

fit_home = 'http://binf1.memphis.edu/fitkids/'
fit_tools = fit_home +'tools/'
cache_dir = '/tmp/'

# fitkids tools
bmi_calcu = fit_tools + 'bmi_calculator/'
assessment = fit_tools + 'fit_assessment/'
mapit = fit_home + 'gis/'
tagita = fit_tools + 'tagita/'

# if a certain page is up
def ping_page(pageurl):
    try:
        response = urllib.request.urlopen(pageurl)
        return (True, None)
    except urllib.error.URLError as urlexception:
        return (False, urlexception.reason)

# get a page cache
def curl_get(pageurl, cache_filename):
    curl_cmd = 'curl {} -s -o {}'.format(pageurl, cache_dir + cache_filename)
    try:
        check_call(curl_cmd.split())
    except CalledProcessError as curlerror:
        print (curlerror)
        exit(1)

def curl_post(pageurl, data, cache_filename):
    curl_cmd = 'curl --data {} {} -s -o {}'.format(data, pageurl, cache_filename)
    try:
        check_call(curl_cmd.split())
    except CalledProcessError as curlerror:
        print (curlerror)
        exit(1)

# tool testing funcs
def test_bmi(tool_url):
    # entries in POST
    class bmi_POST:
        # append result generating script name
        entry_names = ['weight', 'height', 'age', 'gender', 'childname']
        def __init__(self):
            # 0+
            self.weight = 0
            # 0+
            self.height = 0
            # 3-20
            self.age = 0
            #'boy' 'girl'
            self.gender = None
            self.childname = 'binfbot'
            self.entries = {}

        def print_entries(self):
            print ('\n')
            for entry in self.entries.items():
                print (entry)

        def fill_values(self):
            self.weight = random.randint(1, 300)
            self.height = random.randint(1, 100)
            self.age = random.randint(3, 20)
            self.gender = random.choice(['boy', 'girl'])
            self.entries = {'weight':self.weight, 'height':self.height, 'age':self.age, 'childname':self.childname}

    # generate post data
    post = bmi_POST()
    post.fill_values()
    post.print_entries()

    # post it and cache the result
    query_data = urllib.parse.urlencode(post.entries)
    result_page = tool_url + 'results.php'
    cache_name = cache_dir + 'fitkids_bmi_testcache.html'
    curl_post(result_page, query_data, cache_name)
    
    # TODO parse result and match with data posted



def test_assessment(tool_url):
    # entries in POST
    class fit_POST:
        entry_names = ['weight', 'height', 'age', 'gender', 'breakfast', 'fastfood', 'familyeat', 'childdrink', 'childdairy', 'milktype', 'fruit', 'sports', 'tv', 'active', 'bedroom', 'wakeup', 'mama_bear', 'childname']
        def __init__(self):
            self.weight = 0
            self.height = 0
            self.age = 0
            self.gender = None
            self.breakfast = 0
            self.fastfood = 0
            self.familyeat = 0
            self.childdrink = None
            self.childdairy = 0
            self.milktype = None
            self.fruit = None
            self.sports = 0
            self.tv = 0
            self.active = 0
            self.bedroom = None
            self.bedtime = 0
            self.wakeup = 0
            self.mama_bear = None
            self.childname = 'binfbot'
            self.entries = {}
        
        def fill_values(self):
            # 0+
            # 0+
            # 3-20
            #'boy' 'girl'
            # 0-7
            # 0:0-1, 1:2-4, else: 5+
            # 0+
            # 'yes' 'no'
            # 0+
            # 'skim' '1%' '2%' 'whole' 'none' else: non-dairy 
            # 'yes' 'no'
            # 0:0-30, 1:30-60, else:60+
            # 0:0-2, else:3+
            # 0-7
            # 'yes' 'no'
            # -5-3
            # 5-10
            self.weight = random.randint(1, 300)
            self.height = random.randint(1, 100)
            self.age = random.randint(3, 20)
            self.gender = random.choice(['boy', 'girl'])
            self.breakfast = random.randint(0, 7)
            self.fastfood = random.randint(0, 2)
            self.familyeat = random.randint(0, 21)
            self.childdrink = random.choice(['yes','no'])
            self.childdairy = random.randint(0, 20)
            self.milktype = random.choice(['skim','1%','2%','whole','none'])
            self.fruit = random.choice(['yes', 'no'])
            self.sports = random.randint(0, 2)
            self.tv = random.randint(0, 1)
            self.active = random.randint(0, 7)
            self.bedroom = random.choice(['yes', 'no'])
            self.bedtime = random.randint(-5, 3)
            self.wakeup = random.randint(5, 10)
            self.entries = {'weight':self.weight, 'height':self.height, 'age':self.age, 'gender':self.gender, 'breakfast':self.breakfast, 'fastfood':self.fastfood, 'familyeat':self.familyeat, 'childdrink':self.childdrink, 'childdairy':self.childdairy, 'milktype':self.milktype, 'fruit':self.fruit, 'sports':self.sports, 'tv':self.tv, 'active':self.active, 'bedroom':self.bedroom, 'bedtime':self.bedtime,'wakeup':self.wakeup, 'mama_bear':self.mama_bear, 'childname':self.childname}

        def print_entries(self):
            print ('\n')
            for entry in self.entries.items():
                print (entry)

    # generate post data
    post = fit_POST()
    post.fill_values()
    post.print_entries()

    # post it and cache the result
    query_data = urllib.parse.urlencode(post.entries)
    result_page = tool_url + 'results.php'
    cache_name = cache_dir + 'fitkids_assessment_testcache.html'
    curl_post(result_page, query_data, cache_name)
    
    # TODO parse result and match with data posted

def test_mapit(tool_url):
    pass

def test_tagita(tool_url):
    pass

# organize tool testing funcs
class tool_tester:
    def __init__(self):
        self.tools = [bmi_calcu, assessment]
        self.tests = [test_bmi, test_assessment]
        # key: tool_url, value: test_func
        self.suites = { k:v for (k,v) in zip(self.tools, self.tests)}

    def run_test(self, tool_url):
        test_func = self.suites[tool_url]
        test_func(tool_url)

    def run_all_tests(self):
        with concurrent.futures.ThreadPoolExecutor(max_workers=len(self.tools)) as tester:
            # a dictionary where key: func exectution, value: func args
            futures = dict( (tester.submit(v, k), k) for k, v in self.suites.items() )

            for future in concurrent.futures.as_completed(futures):
                tool_url = futures[future]
                if future.exception() is not None:
                    print ('Exception %s when testing %s' % (future.exception(), tool_url))
                else:
                    print ('Test finish on %s' % tool_url)


# test user register and login
class account_tester:
    def __init__(self):
        pass

if __name__=='__main__':
    #result = ping_page(fit_home)
    #print (result)
    #curl_get(fit_home, 'fitkids_testcache.html')
    
    toolbot = tool_tester()
    toolbot.run_all_tests()

    #for tool in toolbot.tools:
    #    toolbot.run_test(tool)

    #for suite in toolbot.suites.items():
    #    print (suite)
    #test_bmi(bmi_calcu)
    #test_assessment(assessment)

