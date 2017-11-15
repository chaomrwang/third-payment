/*
Navicat MySQL Data Transfer

Source Server         : dev
Source Server Version : 100119
Source Host           : localhost:3306
Source Database       : xxtmall

Target Server Type    : MYSQL
Target Server Version : 100119
File Encoding         : 65001

Date: 2017-11-15 10:48:09
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for deal_orders_0
-- ----------------------------
DROP TABLE IF EXISTS `deal_orders_0`;
CREATE TABLE `deal_orders_0` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `order_sn` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '订单号',
  `business` int(4) NOT NULL DEFAULT '0' COMMENT '订单业务(0:购买会员,1:订阅号打赏,2:购买粉钻)',
  `good_id` int(11) NOT NULL COMMENT '商品id',
  `good_name` varchar(128) NOT NULL DEFAULT '' COMMENT '商品名称',
  `good_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `good_discount` int(12) NOT NULL DEFAULT '0' COMMENT '商品优惠',
  `good_num` int(12) NOT NULL DEFAULT '1' COMMENT '商品数量',
  `order_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '订单价格',
  `third_order` varchar(256) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '第三方订单号',
  `third_extend` text CHARACTER SET utf8 COMMENT '第三方扩展',
  `third_type` int(4) NOT NULL DEFAULT '0' COMMENT '第三方类型',
  `channel` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '支付渠道',
  `platform` int(4) NOT NULL DEFAULT '0' COMMENT '支付平台(0:android,1:ios,2:H5)',
  `source` varchar(64) CHARACTER SET sjis NOT NULL DEFAULT '' COMMENT '页面跳转来源',
  `order_status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态（0：未付款；1：待发货；3：待收货；3：待评论；4：已完成；5：待退款）',
  `pay_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '支付状态(0:未付款,1:已付款)',
  `pay_time` int(12) NOT NULL DEFAULT '0' COMMENT '付款时间',
  `extra` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '额外字段',
  `update_time` int(12) NOT NULL COMMENT '更新时间',
  `create_time` int(12) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ip` varchar(128) NOT NULL COMMENT 'ip',
  `str1` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '预留位置1',
  `str2` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '预留位置2',
  `str3` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '预留位置3',
  `str4` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '预留位置4',
  `int1` int(12) NOT NULL DEFAULT '0' COMMENT '预留位置5',
  `int2` int(12) NOT NULL DEFAULT '0' COMMENT '预留位置6',
  `int3` int(12) NOT NULL DEFAULT '0' COMMENT '预留位置7',
  `int4` int(12) NOT NULL DEFAULT '0' COMMENT '预留位置8',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_sn` (`order_sn`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `third_order` (`third_order`(255)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='订单表';

-- ----------------------------
-- Table structure for deal_orders_main_0
-- ----------------------------
DROP TABLE IF EXISTS `deal_orders_main_0`;
CREATE TABLE `deal_orders_main_0` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `order_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `order_sn` varchar(128) DEFAULT NULL COMMENT '订单号',
  `order_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '订单价格',
  `business` int(4) NOT NULL DEFAULT '0' COMMENT '订单业务(0:购买会员,1:订阅号打赏,2:购买粉钻;3:兑换码)',
  `good_id` int(11) NOT NULL COMMENT '商品id',
  `good_name` varchar(64) NOT NULL DEFAULT '' COMMENT '商品名称',
  `pay_time` int(11) NOT NULL DEFAULT '0' COMMENT '付款时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `pay_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '0是ios內购，1支付宝2微信3兑换码',
  `pay_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '支付状态:0未付款、1已付款',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_sn` (`order_sn`) USING BTREE,
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COMMENT='订单主表';
SET FOREIGN_KEY_CHECKS=1;
