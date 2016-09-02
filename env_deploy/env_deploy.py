#!/usr/bin/python
#coding=utf-8
import os
import re
import sys
from Tools import Tools 

#路径
curPath=os.getcwd()+"/"
rootPath=curPath+"../"
#配置文件
sourceConfEnv=curPath+".env"
desConfEnv=rootPath+".env"
sourceConfConfig=curPath+"config-dev.json"
desConfConfig=rootPath+"h5_www/js/config/config-dev.json"
confList=({"conf_source":sourceConfEnv,"conf_des":desConfEnv},{"conf_source":sourceConfConfig,"conf_des":desConfConfig})
#替换配置文件
tools=Tools()
for conf in confList:
    sourceFile=conf['conf_source']
    desFile=conf['conf_des']
    tools.assignConfFile(sourceFile,desFile)
#composer install
os.chdir(rootPath)
os.system('composer install')
#the project's owner
projectPath=rootPath+"../h5mobile"
os.system('chown -R apache:apache %s'%projectPath);
