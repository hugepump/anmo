<template>
    <div class="lb-staff-goods">
        <top-nav />
        <div class="page-main">
          <div class="form-search">
            <el-form :model="ruleForm" ref="ruleForm" label-width="120px">
                <el-form-item label="我的商城" prop="cardText">
                  <el-radio-group v-model="ruleForm.myMall">
                    <el-radio label="1">开启</el-radio>
                    <el-radio label="2">关闭</el-radio>
                  </el-radio-group>
                </el-form-item>
                <div class="item-tips">后台开启“我的商城”后，员工选择的商品将会展示到该员工的商城里面，若没有选择任何商品将不会展示任何商品</div>
                <el-form-item label="数量限制" prop="cardText">
                  <el-input v-model="ruleForm.limt" placeholder="请输入按钮文案"></el-input>
                </el-form-item>
                <div class="item-tips">当数量限制配置了之后, 员工选择我的商城里面的商品数量上限将由这里的设置决定, 0则不限制</div>
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
        myMall: '1',
        limt: ''
      }
    }
  }
}
</script>

<style lang="scss" scoped>
  .lb-staff-goods{
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
