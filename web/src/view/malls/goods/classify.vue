<template>
    <div class="lb-goods-classify">
        <top-nav />
        <div class="page-main">
          <lb-button type='success'>添加分类</lb-button>
          <el-table
            border
            ref="multipleTable"
            :data="tableData"
            tooltip-effect="dark"
            style="width: 100%"
            @selection-change="handleSelectionChange">
            <el-table-column
              type="selection"
              label="全选"
              width="55">
            </el-table-column>
            <el-table-column
              prop="date"
              label="日期"
              width="180">
            </el-table-column>
            <el-table-column
              prop="name"
              label="姓名"
              width="180">
            </el-table-column>
            <el-table-column
              prop="address"
              label="地址">
            </el-table-column>
            <el-table-column
              prop="address"
              label="地址">
            </el-table-column>
            <el-table-column
              prop="address"
              label="地址">
            </el-table-column>
            <el-table-column
              prop="address"
              label="地址">
            </el-table-column>
            <el-table-column
              prop="address"
              label="地址">
            </el-table-column>
          </el-table>
        </div>
    </div>
</template>

<script>
export default {
  data () {
    return {
      key: '商城'
    }
  }
}
</script>

<style lang="scss" scoped>
  .lb-goods-classify{
    width: 100%;
    .page-main{
      padding: 20px;
    }
  }
</style>
