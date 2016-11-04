SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_order_refunds`;
CREATE TABLE `wst_order_refunds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `refundTo` int(11) NOT NULL DEFAULT '0',
  `refundTradeNo` varchar(100) DEFAULT NULL,
  `refundRemark` varchar(400) NOT NULL,
  `refundTime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderId_2` (`orderId`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


alter table wst_orders drop column  refundRemark;
update wst_privileges set privilegeUrl='admin/orders/toRefund',otherPrivilegeUrl='admin/orders/orderRefund' where privilegeCode='TKDD_04';
INSERT INTO `wst_privileges`(`menuId`,`privilegeCode`,`privilegeName`,`isMenuPrivilege`,`privilegeUrl`,`otherPrivilegeUrl`,`dataFlag`) VALUES ('2', 'ZYDP_00', 'µÇÂ¼×ÔÓªµêÆÌ', '0', 'admin/shops/inself', '', '1');
update wst_sys_configs set fieldValue='1.0.1_161101' where fieldCode='wstVersion';
update wst_sys_configs set fieldValue='100afa69ef269c2b37f2057dc8e91e93' where fieldCode='wstMd5';
