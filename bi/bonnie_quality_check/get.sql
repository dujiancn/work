select op.order_id,op.product_id,c.customer_id,c.email,ope.guest_email from order_product as op left join order_product_eticket as ope on op.order_product_id=ope.order_product_id left join `order` as o on ope.order_id=o.order_id left join `customer` as c on o.customer_id=c.customer_id where o.status=100006 and op.product_departure_date>='2016-01-01' and op.product_id in(2268,20544,426,6441,46236,4145,4135,4610,15903) and c.email not in('xcorders@toursforfun.com','axiang.lin@kaiyuan.de','2355615042@qq.com','297971158@qq.com','huyushan@tuniu.com','2355615072@qq.com','g-np-resv@tuniu.com','xiang.xiao@mangocity.com','america@haiwan.com','xuyan001@byecity.com','2604059730@qq.com','wangdandan@tuniu.com','zhengpei@utourworld.com','xiaofang.rausch@caissa.de','493328345@qq.com','zlsorders@toursforfun.com','liqian@lvmama.com','380990252@qq.com','mary@omegauk.net','xiaolu.mo@mangocity.com','qnorders@toursforfun.com','noellewu@gmail.com','aoxintuniu@toursforfun.cn','ouzhoutuniu@toursforfun.com','tnorders@toursforfun.com','omjorders@toursforfun.com','yoyoorders@toursforfun.com','lmmorders@toursforfun.com','mgorders@toursforfun.com','fqorders@toursforfun.com','hworders@toursforfun.com','kszgorders@toursforfun.com','tcorders@toursforfun.com','xhorders@toursforfun.com','aoxinqunar@toursforfun.com','ouzhouxiecheng@toursforfun.com','ouzhouqiongyou@toursforfun.com','ouzhouzoubianouzhou@toursforfun.com','ouzhoutongcheng@toursforfun.com','ouzhoumafengwo@toursforfun.com','ouzhouqunaer@toursforfun.com','ouzhoubaishitong@toursforfun.com','ouzhoumangguowang@toursforfun.com','ouzhoulvmama@toursforfun.com','aoxintongcheng@toursforfun.com','aoxinlvmama@toursforfun.com','gzhlorders@toursforfun.com','bcorders@toursforfun.com','wkorders@toursforfun.com','ksdgorders@toursforfun.com','bstorders@toursforfun.com','xmbyorders@toursforfun.com','2851351337@qq.com','werorders@toursforfun.com','xxorders@toursforfun.com','cdhworders@toursforfun.com','htorders@toursforfun.com','hqglorders@toursforfun.com','jdorders@toursforfun.com','mfworders@toursforfun.com','mdorders@toursforfun.com','winnie.wang@toursforfun.cn','apple.yang@toursforfun.com','selena.yuan@toursforfun.cn','sky.zhou@toursforfun.cn','yuanxin1124@hotmail.com','47007389@qq.com','chuangyi78@126.com','yxorders@toursforfun.com','aoxinxcorders@toursforfun.com','aoxinkaisa@toursforfun.com','alitrip@toursforfun.cn','lcorders@toursforfun.com','aoxinmfw@toursforfun.com','ouzhoulucheng@toursforfun.com','lxwlorders@toursforfun.com','ouzhoulixing@toursforfun.com','yporders@toursforfun.com','aoxinyporders@toursforfun.com','weixin@toursforfun.com','caissa@caissa.com','zxorders@toursforfun.com','viporders@toursforfun.com','91cgorders@toursforfun.com','ouzhoukszg@toursforfun.com','yhlxorders@toursforfun.com','yqforders@toursforfun.com','yborders@toursforfun.com','ouzhouzls@toursforfun.com','zyxxcorders@toursforfun.com','ouzhouyqforders@toursforfun.com','zgorders@toursforfun.com','mzlorders@toursforfun.com','cqyunshangorders@toursforfun.com','gzsforders@toursforfun.com','kevin@TailorATrip.com','dzorders@toursforfun.com','ayorders@toursforfun.com','mhtorders@toursforfun.com','tffrdctest@163.com','test@toursforfun.com') and c.email not like 'noreply%@toursforfun.com';
