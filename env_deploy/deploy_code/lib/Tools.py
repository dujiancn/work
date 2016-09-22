#!/usr/bin/python
# -*- coding: UTF-8 -*-

import MySQLdb
import sys
import os
import re
import shutil
import ConfigParser
import simplejson

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
            #parse conf and check basic key
            cp = ConfigParser.ConfigParser()
            cp.read(conf_file)          
            conf_key_list = ['deploy','database','project']
            for conf_key in conf_key_list:
                if conf_key not in cp.sections():
                    raise Exception("conf %s is null in file %s" %(conf_key, conf_file))  
            #parse personal variable conf and template host
            deploy_ops = cp.options('deploy')
            if 'personal_conf' in deploy_ops:
                personal_conf_file = cur_path + "conf/" + cp.get('deploy','personal_conf') 
                personal_cp = ConfigParser.ConfigParser()
                personal_cp.read(personal_conf_file)
                if 'personal' in personal_cp.sections():
                    personal_ops = personal_cp.options("personal")
                    for opt in personal_ops:
                        opt = opt.upper()
                        self.__personal_variable[opt] = personal_cp.get('personal',opt)
            if 'db_template_host' in deploy_ops:
                self.__db_template_host = cp.get('deploy','db_template_host')
            #db conn
            self.__init_mysql_conn(cp)
            #project conf array
            for project_conf_key in cp.options('project'):
                project_conf_value = cp.get('project', project_conf_key)
                project_conf_value_dict = simplejson.loads(project_conf_value)
                for key in project_conf_value_dict:
                    project_conf_value_dict[key] = project_conf_value_dict[key].strip() 
                self.__project_conf_list.append(project_conf_value_dict) 
        except Exception as e:
            raise e
   
    def assign_configure_file(self):
        for project_conf in self.__project_conf_list:
            print "========== replace file [%s] ==========" % project_conf['from']
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
                    print "assign key %s" %key
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
     
    def __init_mysql_conn(self, config):
        try:
            self.__conn=MySQLdb.connect(config.get('database','host'),config.get('database','user'),config.get('database','passwd'),config.get('database','name'))
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
