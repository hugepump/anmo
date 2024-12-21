<?php
//获取表前缀
$prefix = longbing_get_prefix();

//每个一个sql语句结束，都必须以英文分号结束。因为在执行sql时，需要分割单个脚本执行。
//表前缀需要自己添加{$prefix} 以下脚本被测试脚本

//@ioncube.dk myk("sha256", "random5676u71113r40011") -> "5277be6f3490a79791b53e40943429dec4313cb42ee7a29b9b6766ae3d886966" RANDOM
$sql = <<<updateSql

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_abnormal_order_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT '0' COMMENT '操作人 0平台',
  `user_type` tinyint(3) DEFAULT '1' COMMENT '1角色 2账号 3代理商',
  `order_id` int(11) DEFAULT '0' COMMENT '订单id',
  `process_id` int(11) DEFAULT '0' COMMENT '流程id',
  `deduct_cash` decimal(10,2) DEFAULT '0.00' COMMENT '扣款金额',
  `status` tinyint(3) DEFAULT '1' COMMENT '1待审核 2同意 3拒绝',
  `text` text COMMENT '审核意见',
  `create_time` bigint(11) DEFAULT '0' COMMENT '提交时间',
  `update_time` bigint(3) DEFAULT '0' COMMENT '修改时间',
  `pass_type` tinyint(3) DEFAULT '0' COMMENT '1同意 2拒绝 0普通非流转设置',
  `top` int(11) DEFAULT '0' COMMENT '用于排序 升序',
  `is_default` tinyint(3) DEFAULT '0' COMMENT '标记异常订单的时候的一个默认状态',
  `is_cancel` tinyint(3) DEFAULT '0' COMMENT '是否拒绝 如果拒绝过会走流转设置流程',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='异常订单审核详情';


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_abnormal_order_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL COMMENT '订单id',
  `type` int(11) DEFAULT '0' COMMENT '异常类型 1技师拒单、2超时接单、3客户差评、4行为规范、5话术规范、6客户投诉、7退车费、8到达后取消、9订单取消、10私收小费、11用户退款、12分城市提交、13其他',
  `status` tinyint(3) DEFAULT '1' COMMENT '最终状态 1待处理 2通过 3未通过',
  `deduct_cash` decimal(10,2) DEFAULT '0.00' COMMENT '扣除金额',
  `is_deduct` tinyint(3) DEFAULT '0' COMMENT '是否已经扣款',
  `customer_text` text COMMENT '客服处理意见',
  `create_time` bigint(11) DEFAULT '0' COMMENT '订单创建时间',
  `end_time` bigint(11) DEFAULT '0' COMMENT '最终完成时间',
  `process_id` int(11) DEFAULT '0' COMMENT '当前流程id',
  `bad_text` text COMMENT '差评原因',
  `first_time` bigint(11) DEFAULT '0' COMMENT '首次处理时间',
  `pass_type` tinyint(3) DEFAULT '0' COMMENT '1同意 2拒绝 0普通非流转设置',
  `is_cancel` tinyint(3) DEFAULT '0' COMMENT '是否拒绝 如果拒绝过会走流转设置流程',
  `first_cancel` tinyint(3) DEFAULT '0' COMMENT '是否以一个流程就拒绝了',
  `is_handle` tinyint(3) DEFAULT '0' COMMENT '是否已经处理 0未处理 1已处理',
  `user_id` int(11) DEFAULT '0' COMMENT '标记异常的人的id',
  `end_user_id` int(11) DEFAULT '0' COMMENT '最终处理人',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='异常订单';


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_abnormal_order_process` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `top` int(11) DEFAULT '0' COMMENT '流程顺序排序值',
  `status` tinyint(3) DEFAULT '1',
  `deduct_status` tinyint(3) DEFAULT '0' COMMENT '是否有扣款权限 1有 0无',
  `type` tinyint(3) DEFAULT '1' COMMENT '1平台 2代理商',
  `create_time` bigint(11) DEFAULT '0' COMMENT '创建时间',
  `update_time` bigint(11) DEFAULT '0' COMMENT '修改时间',
  `sub_type` tinyint(3) DEFAULT '1' COMMENT '1任意人审核进入下环节 2所有人审核',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='异常订单流程';


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_abnormal_order_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `process_id` int(11) DEFAULT '0' COMMENT '流程id',
  `type` tinyint(3) DEFAULT '1' COMMENT '1 角色 2账号 3代理商',
  `user_id` int(11) DEFAULT '0' COMMENT '角色或者代理商id',
  `process_type` tinyint(3) DEFAULT '1' COMMENT '流程类型 1平台 2代理商',
  `wander_id` int(11) DEFAULT '0' COMMENT '流转设置id',
  `pass_type` tinyint(3) DEFAULT '0' COMMENT '1同意 2拒绝 0普通非流转设置',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='异常订单流程关联角色';

CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_abnormal_order_wander` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `process_id` int(11) DEFAULT '0' COMMENT '流程id',
  `pass_type` tinyint(3) DEFAULT '1' COMMENT '1同意 2拒绝',
  `deduct_status` tinyint(3) DEFAULT '0' COMMENT '是否有扣款权限 1有 0无',
  `status` tinyint(3) DEFAULT '1' COMMENT '1开启0关闭',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='异常订单更多属性';

alter table `{$prefix}massage_service_abnormal_order_info` ADD COLUMN `sub_type` tinyint(3) DEFAULT '1' COMMENT '1任意人审核进入下环节 2所有人审核';

alter table `{$prefix}massage_service_abnormal_order_list` ADD COLUMN `info_id` int(11) DEFAULT '0' COMMENT '当前处理的进度id';


CREATE TABLE IF NOT EXISTS `{$prefix}massage_service_abnormal_order_info_handle` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `order_info_id` int(11) DEFAULT '0' COMMENT '异常订单每个流程的id',
  `status` tinyint(3) DEFAULT '2' COMMENT ' 2同意 3拒绝',
  `deduct_cash` decimal(10,2) DEFAULT '0.00' COMMENT '扣款金额',
  `text` text COMMENT '审核意见',
  `create_time` bigint(11) DEFAULT '0' COMMENT '提交时间',
  `process_id` int(11) DEFAULT '0' COMMENT '流程id',
  `order_id` int(11) DEFAULT '0' COMMENT '订单id',
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='处理详情表';


updateSql;


return $sql;