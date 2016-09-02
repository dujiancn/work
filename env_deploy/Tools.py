#!/usr/bin/python
#coding=utf-8
import MySQLdb
import sys
import os
import re

class Tools:
    def __init__(self):
        db_host="192.168.100.200"
        db_port="3306"
        db_user="root"
        db_passwd="tufeng1801"
        db_dbname="env_deploy"
        try:
            self.__conn=MySQLdb.connect(db_host,db_user,db_passwd,db_dbname)
        except MySQLdb.Error,e:
            print "Mysql Error %d: %s" % (e.args[0], e.args[1])

    def __getValue(self,tableName,key):
        if 'variable'==tableName:
            sql = "select value from variable where variable_id=%s"%key
        else:
            sql = "select domain_name from env where env_id=%s"%key
        cursor=self.__conn.cursor()
        cursor.execute(sql)
        res=cursor.fetchone()
        cursor.close()
        return res
	
    def assignConfFile(self,sourceFile,desFile):
    	if os.path.exists(desFile):
    	    os.remove(desFile)
    	sourceObj=file(sourceFile)
    	desObj=file(desFile,'w')

    	while 1:
    	    line=sourceObj.readline()
    	    if not line:
    	        break
    	    pattern=re.compile('.*@@(.*)@@(.*)@@')
    	    matchRes=pattern.match(line)
    	    if matchRes:
    	        res=self.__getValue(matchRes.group(1),matchRes.group(2))[0]
    	        pattern=re.compile('@@(.*)@@(.*)@@')
    	        line=re.sub(pattern,res,line)
    	    desObj.write(line)

    	sourceObj.close()
    	desObj.close()
