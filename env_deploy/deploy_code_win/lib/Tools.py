#!/usr/bin/python
# -*- coding: UTF-8 -*-

import MySQLdb,sys,os,re,shutil,simplejson
from configobj import ConfigObj

class Tools:
   
    __cur_path = ''
    __conn = False
    __personal_variable = {}
    __db_template_host = ''   
    __project_conffile_dict = {}
 
    def __init__(self, cur_path, conf_file):
        try:
            if not os.path.exists(conf_file):
                raise Exception("conf is not existed %s"%conf_file)  
            if not os.path.exists(cur_path):
                raise Exception("path is not existed %s"%cur_path)  
            self.__cur_path = cur_path
            #parse conf and check basic key
            config = ConfigObj(conf_file)
            conf_key_list = ['deploy','database','project']
            for conf_key in conf_key_list:
                if not config.has_key(conf_key):
                    raise Exception("conf %s is null in file %s" %(conf_key, conf_file))  
            #parse personal variable conf and template host
            deploy_ops = config['deploy']
            if deploy_ops.has_key('personal_conf'):
                personal_conf_file = "{0}{1}{2}".format(cur_path,"conf/",deploy_ops['personal_conf'])
                personal_config = ConfigObj(personal_conf_file)
                if personal_config.has_key('personal'):
                    self.__personal_variable = personal_config["personal"]
            if deploy_ops.has_key('db_template_host'):
                self.__db_template_host = deploy_ops['db_template_host']
            #db conn
            db_ops = config['database']
            self.__init_mysql_conn(db_ops)
            #project conf array
            self.__project_conffile_dict = config['project']
        except Exception as e:
            raise e
   
    def assign_configure_file(self):
        for key in self.__project_conffile_dict:
            print "========== replace file [%s] ==========" % key
            from_file = self.__project_conffile_dict[key]['from']
            to_file = self.__project_conffile_dict[key]['to']
            source_file = "{0}{1}{2}".format(self.__cur_path, "template/", from_file)
            temp_file = "{0}{1}{2}".format(self.__cur_path, "result/", from_file)
            des_file = "{0}{1}".format(self.__cur_path, to_file)
            
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
                        raise Exception("the key:%s value is not found!" %key)
                    pattern = re.compile('@@(.*)@@')
                    line = re.sub(pattern, value, line)
                temp_fh.write(line)
            source_fh.close()
            temp_fh.close()
            #copy configure file
            shutil.copyfile(temp_file,des_file) 
     
    def __init_mysql_conn(self, db_config):
        try:
            self.__conn=MySQLdb.connect(db_config['host'],db_config['user'],db_config['passwd'],db_config['name'])
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
