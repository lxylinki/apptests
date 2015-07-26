#!/bin/bash
SERVER=binf1.memphis.edu
APP=CollaboratUM
BASEURL=http://binf1.memphis.edu/Collaboratum/
CACHEDIR=/tmp/

DEVEMAIL=collaboratum@binf1.memphis.edu
MYEMAIL=no-reply@binf.memphis.edu

TRUE=1
FALSE=0

INTERVAL=90

#services
LSI_PORT=50005
KWD_PORT=50004

# shared by both services
result=$TRUE

subject="subject:$APP Alert

"

lsi_report="
Dear developers of $APP:

    The LSI query server at $LSI_PORT for your application is down.

    We hope you could look into the problem at your earliest convenience, thank you.


--binfbot
"

kwd_report="
Dear developers of $APP:

    The Keyword query server at $KWD_PORT for your application is down.

    We hope you could look into the problem at your earliest convenience, thank you.


--binfbot
"

# take port num as argument
ifserviceup() {
    PORT=$1

    # simulate input 'ctrl-]' to telnet
    echo 1D | xxd -r -p | telnet $SERVER $PORT >& /dev/null

    if [ $? = 0 ]; then
        echo "Service at $PORT OK."
        result=$TRUE
    else
        echo "Service at $PORT not available."
        result=$FALSE
    fi
}

# terms for query
SEARCHBOX=("autism" "breast" "cancer" "obesity")

# LSI (Conceptual): search type 0
# KWD (Exact): search type 1
SEARCHTYPE=(1 0)

# Everything: filter type 0
# Grants: filter type 1
# Collaborators: filter type 2
# Classes: filter type 3
FILTERTYPE=(0 1 2 3)

# POST form
POST_query() {
    # get array lengths
    search_terms=${#SEARCHBOX[*]}
    search_types=${#SEARCHTYPE[*]}
    filter_types=${#FILTERTYPE[*]}
    
    # random num generator
    rd=$RANDOM

    # POST form data
    DATA="searchBox=${SEARCHBOX[$((rd %= $search_terms))]}&searchType=${SEARCHTYPE[$((rd %= $search_types))]}&filterType=${FILTERTYPE[$((rd %= $filter_types))]}&isFlashEnabled="false""
    
    VIEWPAGE=""$BASEURL"views/results.php"
    
    # retrieve result page
    curl --data $DATA $VIEWPAGE -o ""$CACHEDIR"Collaboratum_testquery_result.html"
}


# fails counter for both services
lsi_failcount=0
kwd_failcount=0

MAXFAILS=3

while true
do
    # lsi service test
    ifserviceup $LSI_PORT
    if [ $result = $FALSE ]; then
        lsi_failcount=$((lsi_failcount+1))
    else
        lsi_failcount=0
    fi
    if [ $lsi_failcount = $MAXFAILS ]; then
        echo $"$subject" $"$lsi_report" | sendmail -f $MYEMAIL -t $DEVEMAIL
    fi

    # keyword service test
    ifserviceup $KWD_PORT
    if [ $result = $FALSE ]; then
        kwd_failcount=$((lsi_failcount+1))
    else
        kwd_failcount=0
    fi
    if [ $kwd_failcount = $MAXFAILS ]; then
        echo $"$subject" $"$kwd_report" | sendmail -f $MYEMAIL -t $DEVEMAIL
    fi

    # post a query to fetch the result page
    if [ $result = $TRUE ]; then
        POST_query
    fi

    sleep $INTERVAL
done
#curl --trace-ascii ./cache/Collaboratum_home.log --trace-time $BASEURL
#curl $BASEURL -o ./cache/Collaboratum_home.html
