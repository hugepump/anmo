<template>
    <div class="lb-pick-up">
        <el-form :model="ruleForm" ref="ruleForm" label-width="120px">
            <el-form-item label="订单超时" prop="cardText">
            <el-input v-model="ruleForm.orderOverTime" placeholder="请输入名片文案"></el-input>
            </el-form-item>
            <div class="item-tips">订单未支付超时时间, 超时将自动取消订单, 单位: 秒</div>
            <el-form-item label="拼团超时" prop="word">
            <el-input v-model="ruleForm.assembleOverTime" placeholder="请输入免审口令"></el-input>
            </el-form-item>
            <div class="item-tips">拼团未成功超时时间, 超时将自动取消订单并退出拼团, 单位: 秒</div>
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
        orderOverTime: '',
        assembleOverTime: ''
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
