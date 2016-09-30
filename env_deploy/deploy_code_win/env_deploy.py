#!/usr/bin/python
# -*- coding: UTF-8 -*-

import os,sys
from lib.Tools import * 

def main():
    try:
        #路径
        cur_path = os.getcwd() + "/"
        conf_file = cur_path + "conf/deploy.conf"
        #tools
        tools = Tools(cur_path, conf_file)
        tools.assign_configure_file()
    except Exception as e:
        print e    
        sys.exit()
    print "deploy success!"

if __name__ == "__main__":
    main()
