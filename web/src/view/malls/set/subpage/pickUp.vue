<template>
    <div class="lb-pick-up">
        <el-form :model="ruleForm" ref="ruleForm" label-width="120px">
            <el-form-item label="自动收货">
                <el-input v-model="ruleForm.day" placeholder="请输入天数"></el-input>
            </el-form-item>
            <div class="item-tips">后台发货后, 用户没有确认收货到时间后自动收货, 最少为5天, 单位: 天</div>
            <el-form-item label="核销密码">
                <el-input v-model="ruleForm.pwd" placeholder="请输入核销密码"></el-input>
            </el-form-item>
            <div class="item-tips">此密码用于在小程序端核销用户订单, 在小程序员工端核销订单处点击扫描客户的订单二维码并且正确输入此密码就能直接完成此订单, 请谨慎操作。并且知道此密码的人都能完成核销订单操作，请注意保管密码以及及时更新密码</div>
            <el-form-item label="自提商品">
                <el-input v-model="ruleForm.goods" placeholder="请输入内容"></el-input>
            </el-form-item>
            <div class="item-tips">购物车自提商品文字提示内容</div>
            <el-form-item>
                <lb-button type='success'>保存</lb-button>
                <lb-button>重置</lb-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
export default {
  data () {
    return {
      ruleForm: {
        day: '',
        pwd: '',
        goods: ''
      }
    }
  }
}
</script>

<style lang="scss" scoped>
    .lb-pick-up{
        width: 100%;
        .el-form{
            width: 100%;
            .el-form-item{
            margin-top: 10px;
            .el-input{
                width: 300px;
            }
            }
            .last-form-item{
            margin-top: 30px;
            }
            .item-tips{
            margin-left: 120px;
            color: #999999;
            }
        }
    }
</style>
