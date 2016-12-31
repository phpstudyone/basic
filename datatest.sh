#!/bin/bash
for (( i = 0; i < 60; i=(i+1) )); do
	/var/www/html/basic/yii hello/data-test >>/var/www/html/sqlLog.txt 2>&1
	sleep 1
done
