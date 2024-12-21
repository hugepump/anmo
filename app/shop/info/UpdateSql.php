<?php
//获取表前缀
$prefix = longbing_get_prefix();

//每个一个sql语句结束，都必须以英文分号结束。因为在执行sql时，需要分割单个脚本执行。
//表前缀需要自己添加{$prefix} 以下脚本被测试脚本


$sql = <<<updateSql

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_name` varchar(64) DEFAULT '',
  `mobile` varchar(32) DEFAULT '',
  `province` varchar(64) DEFAULT '',
  `city` varchar(64) DEFAULT '',
  `area` varchar(64) DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `lng` varchar(64) DEFAULT '0',
  `lat` varchar(64) DEFAULT '0',
  `address` varchar(255) DEFAULT '',
  `top` int(11) DEFAULT '0',
  `create_time` int(11) DEFAULT '0',
  `address_info` varchar(625) DEFAULT '',
  `user_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_balance_card` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT '' COMMENT '标题',
  `price` double(10,2) DEFAULT '0.00' COMMENT '售卖价格',
  `true_price` double(10,2) DEFAULT '0.00' COMMENT '实际价格',
  `top` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '1',
  `create_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_balance_order_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `order_code` varchar(255) DEFAULT '',
  `transaction_id` varchar(255) DEFAULT '',
  `pay_price` double(10,2) DEFAULT '0.00',
  `sale_price` double(10,2) DEFAULT '0.00',
  `true_price` double(10,2) DEFAULT '0.00',
  `create_time` bigint(12) DEFAULT '0',
  `pay_time` bigint(12) DEFAULT '0',
  `status` tinyint(3) DEFAULT '1',
  `title` varchar(255) DEFAULT '',
  `card_id` int(11) DEFAULT '0',
  `now_balance` double(10,2) DEFAULT '0.00' COMMENT '当前余额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;







CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_balance_refund_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `order_code` varchar(255) DEFAULT '',
  `transaction_id` varchar(255) DEFAULT '',
  `card_id` int(11) DEFAULT '0',
  `order_id` int(11) DEFAULT '0',
  `apply_price` double(10,2) DEFAULT '0.00',
  `refund_price` double(10,2) DEFAULT '0.00',
  `title` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '1',
  `create_time` bigint(11) DEFAULT '0',
  `sh_time` bigint(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;





CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_balance_water` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `order_id` int(11) DEFAULT '0',
  `type` int(11) DEFAULT '1' COMMENT '1充值,2消费',
  `add` tinyint(3) DEFAULT '0',
  `price` double(10,2) DEFAULT '0.00' COMMENT '多少钱',
  `create_time` bigint(12) DEFAULT '0',
  `before_balance` double(10,2) DEFAULT '0.00',
  `after_balance` double(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_banner` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `img` varchar(128) DEFAULT '',
  `top` int(11) DEFAULT '1',
  `link` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_car` (
   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `coach_id` int(11) DEFAULT '0',
  `service_id` int(11) DEFAULT '0',
  `num` int(11) DEFAULT '1',
  `status` tinyint(3) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_car_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `distance_free` double(11,2) DEFAULT '0.00' COMMENT '多少公里免费',
  `start_price` double(10,2) DEFAULT '9.00' COMMENT '起步价',
  `start_distance` double(10,2) DEFAULT '9.00' COMMENT '起步距离',
  `distance_price` double(10,2) DEFAULT '1.90' COMMENT '每公里多少钱',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coach_collect` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `coach_id` int(11) DEFAULT '0',
  `create_time` bigint(12) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coach_level` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT '',
  `time_long` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '1',
  `create_time` bigint(12) DEFAULT '0',
  `balance` int(11) DEFAULT '0' COMMENT '抽成比例',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coach_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `coach_name` varchar(255) DEFAULT '' COMMENT '名称',
  `user_id` int(11) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '1',
  `create_time` bigint(11) DEFAULT '0',
  `sex` tinyint(3) DEFAULT '0' COMMENT '性别',
  `work_time` bigint(12) DEFAULT '0' COMMENT '从业年份',
  `city` varchar(255) DEFAULT '' COMMENT '城市',
  `lng` varchar(255) DEFAULT '',
  `lat` varchar(255) DEFAULT '',
  `address` varchar(625) DEFAULT '' COMMENT '详细地址',
  `text` varchar(625) DEFAULT '' COMMENT '简介',
  `id_card` varchar(625) DEFAULT '',
  `license` varchar(625) DEFAULT '' COMMENT '执照',
  `work_img` varchar(255) DEFAULT '' COMMENT '工作照',
  `self_img` text COMMENT '个人照片',
  `is_work` tinyint(3) DEFAULT '1' COMMENT '是否工作',
  `start_time` varchar(32) DEFAULT '',
  `end_time` varchar(32) DEFAULT '',
  `service_price` double(10,2) DEFAULT '0.00',
  `car_price` double(10,2) DEFAULT '0.00',
  `id_code` varchar(255) DEFAULT '',
  `sh_text` varchar(1024) DEFAULT '',
  `sh_time` bigint(11) DEFAULT '0',
  `star` double(10,1) DEFAULT '5.0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coach_police` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `coach_id` int(11) DEFAULT '0',
  `user_id` int(11) DEFAULT '0',
  `text` varchar(1024) DEFAULT '',
  `create_time` bigint(11) DEFAULT '0',
  `have_look` tinyint(3) DEFAULT '0',
  `status` tinyint(3) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_comment_lable` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT '0',
  `lable_id` int(11) DEFAULT NULL,
  `lable_title` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coupon` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT '' COMMENT '名称',
  `type` tinyint(3) DEFAULT '0',
  `full` double(10,2) DEFAULT NULL COMMENT '满多少',
  `discount` double(10,2) DEFAULT NULL COMMENT '减多少',
  `rule` text COMMENT '规则',
  `text` text COMMENT '详情',
  `send_type` tinyint(3) DEFAULT '0' COMMENT '派发方式',
  `time_limit` tinyint(3) DEFAULT '0' COMMENT '时间限制',
  `start_time` bigint(12) DEFAULT '0',
  `end_time` bigint(12) DEFAULT '0',
  `day` int(11) DEFAULT '0' COMMENT '有效期',
  `status` tinyint(3) DEFAULT '1',
  `create_time` bigint(12) DEFAULT '0',
  `top` int(11) DEFAULT '0',
  `stock` int(11) DEFAULT '0' COMMENT '库存',
  `have_send` int(11) DEFAULT '0' COMMENT '已发多少张',
  `i` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}shequshop_school_goods_sh_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `sh_id` int(11) DEFAULT '0' COMMENT '审核id',
  `goods_id` int(11) DEFAULT '0',
  `goods_name` varchar(255) DEFAULT '' COMMENT '商品列表',
  `cate_id` int(11) DEFAULT '0' COMMENT '分类id',
  `cover` varchar(255) DEFAULT '' COMMENT '封面图',
  `imgs` text COMMENT '轮播图',
  `text` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coupon_atv` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `status` tinyint(3) DEFAULT '0',
  `start_time` bigint(12) DEFAULT '0',
  `end_time` bigint(12) DEFAULT '0',
  `inv_user_num` int(11) DEFAULT '0' COMMENT '邀请好友数量',
  `inv_time` int(11) DEFAULT '0' COMMENT '邀请有效期',
  `atv_num` int(11) DEFAULT '0' COMMENT '发起活动次数',
  `inv_user` int(11) DEFAULT '0' COMMENT '邀请人',
  `to_inv_user` int(11) DEFAULT '0' COMMENT '被邀请人',
  `share_img` varchar(625) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coupon_atv_coupon` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `atv_id` int(11) DEFAULT '0',
  `coupon_id` int(11) DEFAULT '0',
  `num` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coupon_atv_record` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `atv_id` int(11) DEFAULT '0' COMMENT '目前只有一个活动,没有什么用',
  `atv_start_time` bigint(11) DEFAULT '0',
  `atv_end_time` bigint(11) DEFAULT NULL,
  `inv_user_num` int(11) DEFAULT '0' COMMENT '邀请好友数量',
  `inv_time` int(11) DEFAULT '0' COMMENT '有效期',
  `end_time` bigint(11) DEFAULT '0',
  `start_time` bigint(11) DEFAULT '0',
  `inv_user` tinyint(3) DEFAULT '0',
  `to_inv_user` tinyint(3) DEFAULT '0',
  `status` tinyint(3) DEFAULT '1',
  `num` int(11) DEFAULT '1',
  `share_img` varchar(625) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coupon_atv_record_coupon` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `atv_id` int(11) DEFAULT '0',
  `record_id` int(11) DEFAULT '0' COMMENT '发起的活动id',
  `coupon_id` int(11) DEFAULT '0' COMMENT '优惠券id',
  `num` int(11) DEFAULT '1' COMMENT '张数',
  `status` int(11) DEFAULT '1',
  `success_num` int(11) DEFAULT '0' COMMENT '已发多少张',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coupon_atv_record_list` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `record_id` int(11) DEFAULT '0',
  `user_id` int(11) DEFAULT '0' COMMENT '发起人',
  `to_inv_id` int(11) DEFAULT '0' COMMENT '被邀请人',
  `create_time` bigint(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coupon_goods` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `coupon_id` int(11) DEFAULT '0',
  `goods_id` int(11) DEFAULT '0',
  `type` int(11) DEFAULT '0' COMMENT '0平台，用户领取',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_coupon_record` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `coupon_id` int(11) DEFAULT '0',
  `title` varchar(225) DEFAULT '',
  `type` int(11) DEFAULT '0',
  `full` double(10,2) DEFAULT '0.00',
  `discount` double(10,2) DEFAULT '0.00',
  `start_time` bigint(12) DEFAULT '0',
  `end_time` bigint(13) DEFAULT '0',
  `status` tinyint(3) DEFAULT '1',
  `create_time` bigint(12) DEFAULT '0',
  `num` int(11) DEFAULT '1',
  `use_time` bigint(11) DEFAULT '0',
  `order_id` int(11) DEFAULT '0',
  `pid` int(11) DEFAULT '0',
  `rule` text,
  `text` text,
  `is_show` tinyint(3) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_lable` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT '',
  `top` int(11) DEFAULT '0',
  `create_time` bigint(11) DEFAULT '0',
  `status` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_order_address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT '0',
  `user_name` varchar(64) DEFAULT '',
  `mobile` varchar(32) DEFAULT '',
  `province` varchar(64) DEFAULT '',
  `city` varchar(64) DEFAULT '',
  `area` varchar(64) DEFAULT '',
  `lng` varchar(32) DEFAULT '0',
  `lat` varchar(32) DEFAULT '0',
  `address` varchar(255) DEFAULT '',
  `address_info` varchar(625) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_order_comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `order_id` int(11) DEFAULT NULL,
  `star` int(255) DEFAULT '0',
  `text` varchar(625) DEFAULT '',
  `create_time` bigint(11) DEFAULT '0',
  `status` int(11) DEFAULT '1',
  `coach_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_order_goods_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `order_id` int(11) DEFAULT '0',
  `goods_id` int(11) DEFAULT '0' COMMENT '服务id',
  `goods_name` varchar(255) DEFAULT '' COMMENT '服务名称',
  `goods_cover` varchar(255) DEFAULT '' COMMENT '封面图',
  `price` double(10,2) DEFAULT '0.00',
  `num` int(11) DEFAULT '1' COMMENT '数量',
  `coach_id` int(11) DEFAULT '0',
  `time_long` int(11) DEFAULT '0',
  `can_refund_num` int(11) DEFAULT '0',
  `true_price` double(10,5) DEFAULT '0.00',
  `pay_type` tinyint(3) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_order_list` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `user_id` int(11) DEFAULT '0',
  `order_code` varchar(255) DEFAULT '' COMMENT '订单号',
  `pay_type` tinyint(3) DEFAULT '1',
  `transaction_id` varchar(64) DEFAULT '' COMMENT '商户订单号',
  `pay_price` double(10,2) DEFAULT '0.00',
  `car_price` double(10,2) DEFAULT '0.00' COMMENT '出行费用',
  `true_car_price` double(10,2) DEFAULT '0.00',
  `service_price` double(10,2) DEFAULT '0.00' COMMENT '服务费用',
  `true_service_price` double(10,2) DEFAULT '0.00',
  `coach_id` int(11) DEFAULT '0' COMMENT '服务技师',
  `start_time` bigint(12) DEFAULT '0',
  `end_time` bigint(12) DEFAULT '0',
  `time_long` int(11) DEFAULT '0' COMMENT '服务时长',
  `true_time_long` int(11) DEFAULT '0' COMMENT '真实服务时长出来退款的',
  `pay_time` bigint(12) DEFAULT '0',
  `create_time` bigint(12) DEFAULT '0',
  `text` varchar(625) DEFAULT '',
  `can_tx_time` int(11) DEFAULT '0',
  `can_tx_date` int(11) DEFAULT '0' COMMENT '可提现时间',
  `hx_user` int(11) DEFAULT '0' COMMENT '核销人',
  `have_tx` tinyint(3) DEFAULT '0',
  `balance` double(10,2) DEFAULT '0.00',
  `receiving_time` bigint(11) DEFAULT '0' COMMENT '接单时间',
  `serout_time` bigint(11) DEFAULT '0' COMMENT '出发时间',
  `arrive_time` bigint(11) DEFAULT '0' COMMENT '到达时间',
  `start_service_time` bigint(11) DEFAULT '0' COMMENT '开始服务时间',
  `arrive_img` varchar(625) DEFAULT '' COMMENT '到达拍照',
  `order_end_time` bigint(11) DEFAULT '0' COMMENT '订单核销时间',
  `is_comment` tinyint(3) DEFAULT '0',
  `distance` double(10,2) DEFAULT '0.00' COMMENT '距离',
  `car_type` tinyint(3) DEFAULT '0',
  `coach_refund_time` bigint(11) DEFAULT '0' COMMENT '拒绝接单',
  `coach_refund_code` varchar(64) DEFAULT '',
  `coupon_id` int(11) DEFAULT '0',
  `discount` double(10,2) DEFAULT '0.00' COMMENT '优惠金额',
  `init_service_price` double(10,2) DEFAULT '0.00',
  `over_time` bigint(11) DEFAULT '0',
  `is_show` tinyint(3) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_refund_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `order_code` varchar(64) DEFAULT '',
  `order_id` int(11) DEFAULT '0',
  `user_id` int(11) DEFAULT '0',
  `transaction_id` varchar(64) DEFAULT '',
  `coach_id` int(11) DEFAULT '0' COMMENT '教练',
  `pay_price` double(10,2) DEFAULT '0.00',
  `apply_price` double(10,2) DEFAULT '0.00',
  `refund_price` double(10,2) DEFAULT '0.00',
  `status` int(11) DEFAULT '1',
  `text` varchar(625) DEFAULT '',
  `refund_text` varchar(625) DEFAULT '',
  `create_time` bigint(12) DEFAULT '0',
  `refund_time` bigint(12) DEFAULT '0',
  `balance` double(10,2) DEFAULT '0.00',
  `cancel_time` bigint(11) DEFAULT '0',
  `out_refund_no` varchar(64) DEFAULT '',
  `imgs` varchar(1024) DEFAULT '',
  `car_price` double(10,2) DEFAULT '0.00',
  `time_long` int(11) DEFAULT '0' COMMENT '退款服务时长',
  `service_price` double(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_refund_order_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `refund_id` int(11) DEFAULT '0',
  `goods_id` int(11) DEFAULT '0',
  `goods_name` varchar(255) DEFAULT '',
  `goods_cover` varchar(255) DEFAULT '',
  `goods_price` decimal(10,2) DEFAULT '0.00',
  `num` int(11) DEFAULT '1',
  `order_goods_id` int(11) DEFAULT '0',
  `order_id` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_service_coach` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `ser_id` int(11) DEFAULT '0' COMMENT '服务id',
  `coach_id` int(11) DEFAULT '0' COMMENT '教练id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_service_list` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT '' COMMENT '标题',
  `cover` varchar(255) DEFAULT '' COMMENT '封面图',
  `price` double(10,2) DEFAULT '0.00' COMMENT '价格',
  `sale` int(11) DEFAULT '0' COMMENT '销量',
  `true_sale` int(11) DEFAULT '0' COMMENT '实际销量',
  `total_sale` int(11) DEFAULT '0' COMMENT '总销量',
  `time_long` int(11) DEFAULT '0' COMMENT '服务时长',
  `max_time` int(11) DEFAULT '0' COMMENT '最长预约',
  `introduce` text COMMENT '介绍',
  `explain` text COMMENT '说明',
  `notice` text COMMENT '须知',
  `top` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '1',
  `create_time` bigint(11) DEFAULT '0',
  `star` double(10,2) DEFAULT '5.00',
  `imgs` text,
  `lock` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_user_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `openid` varchar(64) NOT NULL DEFAULT '',
  `nickName` varchar(255) CHARACTER SET utf8mb4 DEFAULT '',
  `avatarUrl` varchar(255) DEFAULT '',
  `create_time` bigint(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '1',
  `cap_id` int(11) DEFAULT '0',
  `city` varchar(255) DEFAULT '',
  `country` varchar(255) DEFAULT '',
  `gender` int(11) DEFAULT '0',
  `language` varchar(32) DEFAULT '',
  `province` varchar(128) DEFAULT '',
  `balance` double(10,2) DEFAULT '0.00' COMMENT '余额',
  `phone` varchar(32) DEFAULT '',
  `session_key` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid` (`openid`,`uniacid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_wallet_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT '',
  `user_id` int(11) DEFAULT '0',
  `coach_id` int(11) DEFAULT NULL,
  `total_price` double(10,2) DEFAULT '0.00' COMMENT '总代提现多少',
  `apply_price` double(10,2) DEFAULT '0.00',
  `service_price` double(10,2) DEFAULT '0.00' COMMENT '手续费',
  `balance` int(11) DEFAULT '0' COMMENT '提成比例',
  `true_price` double(10,2) DEFAULT '0.00' COMMENT '实际到账',
  `status` int(11) DEFAULT '1',
  `create_time` bigint(11) DEFAULT '11',
  `sh_time` bigint(11) DEFAULT '0',
  `type` tinyint(3) DEFAULT '0' COMMENT '1是车费',
  `online` tinyint(3) DEFAULT '0',
  `payment_no` varchar(64) DEFAULT '',
  `text` varchar(1024) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}shequshop_school_admin` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT '',
  `passwd` varchar(255) DEFAULT '',
  `create_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}shequshop_school_attachment` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `filename` varchar(255) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  `module_upload_dir` varchar(100) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `longbing_attachment_path` char(255) NOT NULL DEFAULT '' COMMENT 'path',
  `longbing_driver` char(10) NOT NULL DEFAULT '' COMMENT 'loacl',
  `longbing_from` varchar(255) NOT NULL DEFAULT '' COMMENT 'web',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}shequshop_school_attachment_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `uniacid` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT '0',
  `type` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}shequshop_school_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `appid` varchar(32) DEFAULT '',
  `appsecret` varchar(64) DEFAULT '',
  `app_name` varchar(255) DEFAULT '',
  `over_time` int(11) DEFAULT '300' COMMENT '订单超时取消',
  `cash_balance` int(11) DEFAULT '100' COMMENT ' 提现比列',
  `min_cash` double(10,2) DEFAULT '0.00' COMMENT '最低提现金额',
  `gzh_appid` varchar(64) DEFAULT '',
  `order_tmp_id` varchar(128) DEFAULT '' COMMENT '下单通知',
  `cancel_tmp_id` varchar(128) DEFAULT '',
  `max_day` int(11) DEFAULT '0' COMMENT '最长预约时间',
  `time_unit` int(11) DEFAULT '0' COMMENT '时长单位',
  `service_cover_time` int(11) DEFAULT '0' COMMENT '服务倒计时',
  `can_tx_time` int(11) DEFAULT '24' COMMENT '多少小时后可提现',
  `cash_mini` double(10,2) DEFAULT '0.01',
  `mobile` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}shequshop_school_oos_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `miniapp_name` varchar(50) NOT NULL DEFAULT '',
  `open_oss` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-本地 1-阿里云 2-七牛云  3--腾讯云',
  `aliyun_bucket` varchar(255) NOT NULL DEFAULT '' COMMENT '仓库',
  `aliyun_access_key_id` varchar(50) NOT NULL DEFAULT '' COMMENT '阿里云',
  `aliyun_access_key_secret` varchar(100) NOT NULL DEFAULT '' COMMENT '阿里云',
  `aliyun_base_dir` varchar(200) NOT NULL DEFAULT '' COMMENT '图片等资源存储根目录',
  `aliyun_zidinyi_yuming` varchar(255) NOT NULL DEFAULT '' COMMENT '自定义域名',
  `aliyun_endpoint` varchar(255) NOT NULL DEFAULT '',
  `aliyun_rules` text COMMENT '阿里云的规则配置',
  `qiniu_accesskey` varchar(100) NOT NULL DEFAULT '' COMMENT '七牛云秘钥',
  `qiniu_secretkey` varchar(100) NOT NULL DEFAULT '' COMMENT '七牛云秘钥',
  `qiniu_bucket` varchar(50) NOT NULL DEFAULT '' COMMENT '七牛云仓库',
  `qiniu_yuming` varchar(255) NOT NULL DEFAULT '' COMMENT '七牛自定义域名  前面要加http://',
  `qiniu_rules` text COMMENT '七牛的规则配置',
  `tenxunyun_appid` varchar(20) NOT NULL DEFAULT '' COMMENT '腾讯云的appid',
  `tenxunyun_secretid` varchar(50) NOT NULL DEFAULT '' COMMENT '腾讯云secretid',
  `tenxunyun_secretkey` varchar(50) NOT NULL DEFAULT '' COMMENT '腾讯云的配置',
  `tenxunyun_bucket` varchar(50) NOT NULL DEFAULT '' COMMENT '腾讯云图片仓库',
  `tenxunyun_region` varchar(50) NOT NULL DEFAULT '' COMMENT '腾讯云地域',
  `tenxunyun_yuming` varchar(300) NOT NULL DEFAULT '' COMMENT '腾讯云域名',
  `apiclient_cert` varchar(200) NOT NULL DEFAULT '',
  `apiclient_key` varchar(200) NOT NULL DEFAULT '' COMMENT '两个证书文件路径',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `is_sync` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否同步',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '储蓄名字',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}shequshop_school_pay_config` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uniacid` int(10) NOT NULL DEFAULT '0' COMMENT '小程序关联id',
  `mch_id` varchar(255) NOT NULL DEFAULT '' COMMENT '商户号',
  `pay_key` varchar(255) NOT NULL DEFAULT '' COMMENT '支付秘钥',
  `cert_path` varchar(255) NOT NULL DEFAULT '' COMMENT '证书',
  `key_path` varchar(255) NOT NULL DEFAULT '' COMMENT '证书',
  `min_price` int(6) NOT NULL DEFAULT '0' COMMENT '最低提现金额',
  `pay_name` varchar(255) NOT NULL DEFAULT 'wechat' COMMENT '支付类型',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}shequshop_school_wechat_code` (
`id` char(32) NOT NULL DEFAULT '',
  `uniacid` int(11) NOT NULL DEFAULT '0' COMMENT 'uniacid',
  `data` text COMMENT '数据',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `deleted` tinyint(1) DEFAULT '0' COMMENT '1：已回收；0：可用；',
  `path` varchar(500) DEFAULT '',
  `count` int(11) DEFAULT '0' COMMENT '扫码次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_notice_list` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT '0',
  `have_look` tinyint(3) DEFAULT '0' COMMENT '是否被查看',
  `type` tinyint(3) DEFAULT '1' COMMENT '1是订单，2是退款',
  `create_time` bigint(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_order_commission` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `top_id` int(11) DEFAULT '0' COMMENT '上级id',
  `order_id` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '1',
  `order_code` varchar(255) DEFAULT '',
  `cash` double(10,2) DEFAULT NULL COMMENT '佣金',
  `create_time` bigint(12) DEFAULT '0',
  `update_time` bigint(12) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_order_commission_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `order_goods_id` int(11) DEFAULT '0',
  `commission_id` int(11) DEFAULT '0',
  `cash` double(10,2) DEFAULT NULL,
  `balance` int(11) DEFAULT '0',
  `num` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `{$prefix}massage_service_user_list` ADD COLUMN `pid` int(11) DEFAULT '0';


ALTER TABLE `{$prefix}massage_service_order_commission` ADD COLUMN `cash_time` bigint(11) DEFAULT '0';

updateSql;




return $sql;