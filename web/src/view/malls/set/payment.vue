<template>
    <div class="lb-payment">
        <top-nav />
        <div class="page-main">
          <div class="form-search">
            <el-form :model="ruleForm" ref="ruleForm" label-width="120px">
                <el-form-item label="订单超时" prop="cardText">
                  <el-input v-model="ruleForm.btnText" placeholder="请输入按钮文案"></el-input>
                </el-form-item>
                <div class="item-tips">商城详情页咨询面议文字</div>
                <el-form-item label="IOS虚拟支付" prop="word">
                  <el-radio-group v-model="ruleForm.ios">
                    <el-radio label="1">开启</el-radio>
                    <el-radio label="2">关闭</el-radio>
                  </el-radio-group>
                </el-form-item>
                <div class="item-tips">是否允许小程序IOS端显示支付相关的内容, 关闭后IOS端所有和支付相关的内容不会显示。开启此设置涉及到虚拟支付容易被封禁，请谨慎开启。</div>
                <el-form-item label="android虚拟支付" prop="cardText">
                  <el-radio-group v-model="ruleForm.android">
                    <el-radio label="1">开启</el-radio>
                    <el-radio label="2">关闭</el-radio>
                  </el-radio-group>
                </el-form-item>
                <div class="item-tips">是否允许小程序android端显示支付相关的内容, 关闭后android端所有和支付相关的内容不会显示</div>
                <el-form-item>
                    <lb-button type='success'>保存</lb-button>
                    <lb-button>重置</lb-button>
                </el-form-item>
            </el-form>
          </div>
        </div>
    </div>
</template>

<script>
export default {
  data () {
    return {
      ruleForm: {
        btnText: '',
        ios: '1',
        android: '1'
      }
    }
  }
}
</script>

<style lang="scss" scoped>
  .lb-payment{
    width: 100%;
    .form-search{
      padding: 20px;
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
  }
</style>
