#!/bin/bash
count=0

while [ $count != 10 ]
do
    count=$((count+1))
    echo "$count"
    sleep 1
done

