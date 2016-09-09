#!/usr/bin/python
# -*- coding: UTF-8 -*-

import MySQLdb
import sys
import os
import re
import demjson
import shutil

class Tools:
   
    __cur_path = ''
    __conn = False
    __personal_variable = {}
    __db_template_host = ''   
    __project_conf_list = []
 
    def __init__(self, cur_path, conf_file):
        try:
            if not os.path.exists(conf_file):
                raise Exception("conf is not existed %s"%conf_file)  
            if not os.path.exists(cur_path):
                raise Exception("path is not existed %s"%cur_path)  
            self.__cur_path = cur_path
            #parse conf
            fh = open(conf_file,"r")
            line = fh.readlines()[0]
            fh.close()
            line_json = demjson.decode(line)
            #parse personal variable conf
            if line_json.has_key('personal_conf'):
                personal_conf_file = cur_path + "conf/" + line_json['personal_conf']
                self.__parse_personal_conf(personal_conf_file)
            #db template host
            if line_json.has_key('db_template_host'):
                self.__db_template_host = line_json['db_template_host']
            #db conn
            if not line_json.has_key('database'):
                raise Exception("no db configure has been found!")
            self.__init_mysql_conn(line_json['database'])
            #project conf array
            if (not line_json.has_key('project_conf')) or (not len(line_json['project_conf'])): 
                raise Exception("project configure isn't exits or is null!")
            self.__project_conf_list = line_json['project_conf'] 
        except Exception as e:
            raise e
   
    def assign_configure_file(self):
        for project_conf in self.__project_conf_list:
            source_file = self.__cur_path + "template/" + project_conf['from']
            temp_file = self.__cur_path + "result/" + project_conf['from']
    	    des_file = self.__cur_path + project_conf['to']
            #replace variable 
            source_fh=file(source_file)
    	    temp_fh=file(temp_file,'w')
    	    while True:
    	        line=source_fh.readline()
    	        if not line:
    	            break
    	        pattern=re.compile('.*@@(.*)@@')
    	        match_res=pattern.match(line)
                if match_res:
    	            key = match_res.group(1)
                    value = self.__replace_variable(key)
    	            if '' == value.strip():
                        raise Exception("the key:%s value is not found!")%key
                    pattern = re.compile('@@(.*)@@')
    	            line = re.sub(pattern, value, line)
    	        temp_fh.write(line)
    	    source_fh.close()
    	    temp_fh.close()
            #copy configure file
            shutil.copyfile(temp_file,des_file) 
     
    def __parse_personal_conf(self, personal_conf_file):
        try:
             if not os.path.exists(personal_conf_file):   
                 raise Exception("personal conf is not exist %s"%personal_conf_file)  
             fh = open(personal_conf_file,"r")
             line = fh.readlines()[0]
             fh.close()
             self.__personal_variable = demjson.decode(line)
        except Exception as e:
            raise e

    def __init_mysql_conn(self, db_conf):
        try:
            self.__conn=MySQLdb.connect(db_conf['host'],db_conf['user'],db_conf['passwd'],db_conf['name'])
        except Exception as e:
            raise e

    def __replace_variable(self, key):
        value = ''
        #首先根据个性化配置进行替换
        if self.__personal_variable.has_key(key):
            value = self.__personal_variable[key] 
        #根据数据库变量模板进行配置
        host_name = self.__db_template_host 
        while ('' == value.strip()) and (host_name.strip()):
            sql = "select value from variable where variable_name='%s' and host_name='%s'"%(key, host_name)
            sql_res = self.__get_value(sql)
            if len(sql_res):
                value = sql_res[0]
            sql = "select parent_name from host where name='%s'"%(host_name)
            sql_res = self.__get_value(sql)
            if len(sql_res):
                host_name = sql_res[0]
            else:
                host_name = ""
        return value 

    def __get_value(self, sql):
        cursor=self.__conn.cursor()
        cursor.execute(sql)
        res=cursor.fetchone()
        cursor.close()
        return res
	
